<?php
/**
 * Mockery
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://github.com/padraic/mockery/master/LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to padraic@php.net so we can send you a copy immediately.
 *
 * @category   Mockery
 * @package    Mockery
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/mockery/blob/master/LICENSE New BSD License
 */

class ExpectationTest extends PHPUnit_Framework_TestCase
{

    public function setup ()
    {
        $this->container = new \Mockery\Container;
        $this->mock = $this->container->mock('foo');
    }
    
    public function teardown()
    {
        $this->container->mockery_close();
    }

    public function testReturnsNullWhenNoArgs()
    {
        $this->mock->shouldReceive('foo');
        $this->assertNull($this->mock->foo());
    }
    
    public function testReturnsNullWhenSingleArg()
    {
        $this->mock->shouldReceive('foo');
        $this->assertNull($this->mock->foo(1));
    }
    
    public function testReturnsNullWhenManyArgs()
    {
        $this->mock->shouldReceive('foo');
        $this->assertNull($this->mock->foo('foo', array(), new stdClass));
    }
    
    public function testReturnsSameValueForAllIfNoArgsExpectationAndNoneGiven()
    {
        $this->mock->shouldReceive('foo')->andReturn(1);
        $this->assertEquals(1, $this->mock->foo());
    }
    
    public function testReturnsSameValueForAllIfNoArgsExpectationAndSomeGiven()
    {
        $this->mock->shouldReceive('foo')->andReturn(1);
        $this->assertEquals(1, $this->mock->foo('foo'));
    }
    
    public function testReturnsValueFromSequenceSequentially()
    {
        $this->mock->shouldReceive('foo')->andReturn(1, 2, 3);
        $this->mock->foo('foo');
        $this->assertEquals(2, $this->mock->foo('foo'));
    }
    
    public function testReturnsValueFromSequenceSequentiallyAndRepeatedlyReturnsFinalValueOnExtraCalls()
    {
        $this->mock->shouldReceive('foo')->andReturn(1, 2, 3);
        $this->mock->foo('foo');
        $this->mock->foo('foo');
        $this->assertEquals(3, $this->mock->foo('foo'));
        $this->assertEquals(3, $this->mock->foo('foo'));
    }
    
    public function testReturnsValueFromSequenceSequentiallyAndRepeatedlyReturnsFinalValueOnExtraCallsWithManyAndReturnCalls()
    {
        $this->mock->shouldReceive('foo')->andReturn(1)->andReturn(2, 3);
        $this->mock->foo('foo');
        $this->mock->foo('foo');
        $this->assertEquals(3, $this->mock->foo('foo'));
        $this->assertEquals(3, $this->mock->foo('foo'));
    }

    public function testReturnsValueOfClosure()
    {
        $this->mock->shouldReceive('foo')->with(5)->andReturn(function($v){return $v+1;});
        $this->assertEquals(6, $this->mock->foo(5));
    }
    
    public function testReturnsUndefined()
    {
        $this->mock->shouldReceive('foo')->andReturnUndefined();
        $this->assertTrue($this->mock->foo() instanceof \Mockery\Undefined);
    }
    
    /**
     * @expectedException OutOfBoundsException
     */
    public function testThrowsException()
    {
        $this->mock->shouldReceive('foo')->andThrow(new OutOfBoundsException);
        $this->mock->foo();
    }
    
    /**
     * @expectedException OutOfBoundsException
     */
    public function testThrowsExceptionBasedOnArgs()
    {
        $this->mock->shouldReceive('foo')->andThrow('OutOfBoundsException');
        $this->mock->foo();
    }
    
    public function testThrowsExceptionBasedOnArgsWithMessage()
    {
        $this->mock->shouldReceive('foo')->andThrow('OutOfBoundsException', 'foo');
        try {
            $this->mock->foo();
        } catch (OutOfBoundsException $e) {
            $this->assertEquals('foo', $e->getMessage());
        }
    }
    
    /**
     * @expectedException OutOfBoundsException
     */
    public function testThrowsExceptionSequentially()
    {
        $this->mock->shouldReceive('foo')->andThrow(new Exception)->andThrow(new OutOfBoundsException);
        try {
            $this->mock->foo();
        } catch (Exception $e) {}
        $this->mock->foo();
    }
    
    public function testMultipleExpectationsWithReturns()
    {
        $this->mock->shouldReceive('foo')->with(1)->andReturn(10);
        $this->mock->shouldReceive('bar')->with(2)->andReturn(20);
        $this->assertEquals(10, $this->mock->foo(1));
        $this->assertEquals(20, $this->mock->bar(2));
    }
    
    public function testExpectsNoArguments()
    {
        $this->mock->shouldReceive('foo')->withNoArgs();
        $this->mock->foo();
    }
    
    /**
     * @expectedException \Mockery\Exception
     * @group 1A
     */
    public function testExpectsNoArgumentsThrowsExceptionIfAnyPassed()
    {
        $this->mock->shouldReceive('foo')->withNoArgs();
        $this->mock->foo(1);
    }
    
    public function testExpectsAnyArguments()
    {
        $this->mock->shouldReceive('foo')->withAnyArgs();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 'k', new stdClass);
    }
    
    public function testUsesMockeryScalarConstantPlaceholdersForAnyArgument() //and all scalars
    {
        $this->markTestIncomplete();
        $this->mock->shouldReceive('foo')->with(\Mockery::ANY);
    }
    
    public function testExpectsArgumentMatchingRegularExpression()
    {
        $this->mock->shouldReceive('foo')->with('/bar/i');
        $this->mock->foo('xxBARxx');
    }
    
    public function testExpectsArgumentMatchingObjectType()
    {
        $this->mock->shouldReceive('foo')->with('\stdClass');
        $this->mock->foo(new stdClass);
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testThrowsExceptionOnNoArgumentMatch()
    {
        $this->mock->shouldReceive('foo')->with(1);
        $this->mock->foo(2);
    }
    
    public function testNeverCalled()
    {
        $this->mock->shouldReceive('foo')->never();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testNeverCalledThrowsExceptionOnCall()
    {
        $this->mock->shouldReceive('foo')->never();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testCalledOnce()
    {
        $this->mock->shouldReceive('foo')->once();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testCalledOnceThrowsExceptionIfNotCalled()
    {
        $this->mock->shouldReceive('foo')->once();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testCalledOnceThrowsExceptionIfCalledTwice()
    {
        $this->mock->shouldReceive('foo')->once();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testCalledTwice()
    {
        $this->mock->shouldReceive('foo')->twice();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testCalledTwiceThrowsExceptionIfNotCalled()
    {
        $this->mock->shouldReceive('foo')->twice();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testCalledOnceThrowsExceptionIfCalledThreeTimes()
    {
        $this->mock->shouldReceive('foo')->twice();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testCalledZeroOrMoreTimesAtZeroCalls()
    {
        $this->mock->shouldReceive('foo')->zeroOrMoreTimes();
        $this->mock->mockery_verify();
    }
    
    public function testCalledZeroOrMoreTimesAtThreeCalls()
    {
        $this->mock->shouldReceive('foo')->zeroOrMoreTimes();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testTimesCountCalls()
    {
        $this->mock->shouldReceive('foo')->times(4);
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testTimesCountCallThrowsExceptionOnTooFewCalls()
    {
        $this->mock->shouldReceive('foo')->times(2);
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testTimesCountCallThrowsExceptionOnTooManyCalls()
    {
        $this->mock->shouldReceive('foo')->times(2);
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testCalledAtLeastOnceAtExactlyOneCall()
    {
        $this->mock->shouldReceive('foo')->atLeast()->once();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testCalledAtLeastOnceAtExactlyThreeCalls()
    {
        $this->mock->shouldReceive('foo')->atLeast()->times(3);
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testCalledAtLeastThrowsExceptionOnTooFewCalls()
    {
        $this->mock->shouldReceive('foo')->atLeast()->twice();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testCalledAtMostOnceAtExactlyOneCall()
    {
        $this->mock->shouldReceive('foo')->atMost()->once();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testCalledAtMostAtExactlyThreeCalls()
    {
        $this->mock->shouldReceive('foo')->atMost()->times(3);
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testCalledAtLeastThrowsExceptionOnTooManyCalls()
    {
        $this->mock->shouldReceive('foo')->atMost()->twice();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testExactCountersOverrideAnyPriorSetNonExactCounters()
    {
        $this->mock->shouldReceive('foo')->atLeast()->once()->once();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testComboOfLeastAndMostCallsWithOneCall()
    {
        $this->mock->shouldReceive('foo')->atleast()->once()->atMost()->twice();
        $this->mock->foo();
        $this->mock->mockery_verify(); 
    }
    
    public function testComboOfLeastAndMostCallsWithTwoCalls()
    {
        $this->mock->shouldReceive('foo')->atleast()->once()->atMost()->twice();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify(); 
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testComboOfLeastAndMostCallsThrowsExceptionAtTooFewCalls()
    {
        $this->mock->shouldReceive('foo')->atleast()->once()->atMost()->twice();
        $this->mock->mockery_verify(); 
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testComboOfLeastAndMostCallsThrowsExceptionAtTooManyCalls()
    {
        $this->mock->shouldReceive('foo')->atleast()->once()->atMost()->twice();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify(); 
    }
    
    public function testCallCountingOnlyAppliesToMatchedExpectations()
    {
        $this->mock->shouldReceive('foo')->with(1)->once();
        $this->mock->shouldReceive('foo')->with(2)->twice();
        $this->mock->shouldReceive('foo')->with(3);
        $this->mock->foo(1);
        $this->mock->foo(2);
        $this->mock->foo(2);
        $this->mock->foo(3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\CountValidator\Exception
     */
    public function testCallCountingThrowsExceptionOnAnyMismatch()
    {
        $this->mock->shouldReceive('foo')->with(1)->once();
        $this->mock->shouldReceive('foo')->with(2)->twice();
        $this->mock->shouldReceive('foo')->with(3);
        $this->mock->shouldReceive('bar');
        $this->mock->foo(1);
        $this->mock->foo(2);
        $this->mock->foo(3);
        $this->mock->bar();
        $this->mock->mockery_verify();
    }
    
    public function testOrderedCallsWithoutError()
    {
        $this->mock->shouldReceive('foo')->ordered();
        $this->mock->shouldReceive('bar')->ordered();
        $this->mock->foo();
        $this->mock->bar();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testOrderedCallsWithOutOfOrderError()
    {
        $this->mock->shouldReceive('foo')->ordered();
        $this->mock->shouldReceive('bar')->ordered();
        $this->mock->bar();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testDifferentArgumentsAndOrderingsPassWithoutException()
    {
        $this->mock->shouldReceive('foo')->with(1)->ordered();
        $this->mock->shouldReceive('foo')->with(2)->ordered();
        $this->mock->foo(1);
        $this->mock->foo(2);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testDifferentArgumentsAndOrderingsThrowExceptionWhenInWrongOrder()
    {
        $this->mock->shouldReceive('foo')->with(1)->ordered();
        $this->mock->shouldReceive('foo')->with(2)->ordered();
        $this->mock->foo(2);
        $this->mock->foo(1);
        $this->mock->mockery_verify();
    }
    
    public function testUnorderedCallsIgnoredForOrdering()
    {
        $this->mock->shouldReceive('foo')->with(1)->ordered();
        $this->mock->shouldReceive('foo')->with(2);
        $this->mock->shouldReceive('foo')->with(3)->ordered();
        $this->mock->foo(2);
        $this->mock->foo(1);
        $this->mock->foo(2);
        $this->mock->foo(3);
        $this->mock->foo(2);
        $this->mock->mockery_verify();
    }
    
    public function testOrderingOfDefaultGrouping()
    {
        $this->mock->shouldReceive('foo')->ordered();
        $this->mock->shouldReceive('bar')->ordered();
        $this->mock->foo();
        $this->mock->bar();
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testOrderingOfDefaultGroupingThrowsExceptionOnWrongOrder()
    {
        $this->mock->shouldReceive('foo')->ordered();
        $this->mock->shouldReceive('bar')->ordered();
        $this->mock->bar();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testOrderingUsingNumberedGroups()
    {
        $this->mock->shouldReceive('start')->ordered(1);
        $this->mock->shouldReceive('foo')->ordered(2);
        $this->mock->shouldReceive('bar')->ordered(2);
        $this->mock->shouldReceive('final')->ordered();
        $this->mock->start();
        $this->mock->bar();
        $this->mock->foo();
        $this->mock->bar();
        $this->mock->final();
        $this->mock->mockery_verify();
    }
    
    public function testOrderingUsingNamedGroups()
    {
        $this->mock->shouldReceive('start')->ordered('start');
        $this->mock->shouldReceive('foo')->ordered('foobar');
        $this->mock->shouldReceive('bar')->ordered('foobar');
        $this->mock->shouldReceive('final')->ordered();
        $this->mock->start();
        $this->mock->bar();
        $this->mock->foo();
        $this->mock->bar();
        $this->mock->final();
        $this->mock->mockery_verify();
    }
    
    public function testGroupedUngroupedOrderingDoNotOverlap()
    {
        $s = $this->mock->shouldReceive('start')->ordered();
        $m = $this->mock->shouldReceive('mid')->ordered('foobar');
        $e = $this->mock->shouldReceive('end')->ordered();
        $this->assertTrue($s->getOrderNumber() < $m->getOrderNumber());
        $this->assertTrue($m->getOrderNumber() < $e->getOrderNumber());
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testGroupedOrderingThrowsExceptionWhenCallsDisordered()
    {
        $this->mock->shouldReceive('foo')->ordered('first');
        $this->mock->shouldReceive('bar')->ordered('second');
        $this->mock->bar();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testExpectationMatchingWithNoArgsOrderings()
    {
        $this->mock->shouldReceive('foo')->withNoArgs()->once()->ordered();
        $this->mock->shouldReceive('bar')->withNoArgs()->once()->ordered();
        $this->mock->shouldReceive('foo')->withNoArgs()->once()->ordered();
        $this->mock->foo();
        $this->mock->bar();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testExpectationMatchingWithAnyArgsOrderings()
    {
        $this->mock->shouldReceive('foo')->withAnyArgs()->once()->ordered();
        $this->mock->shouldReceive('bar')->withAnyArgs()->once()->ordered();
        $this->mock->shouldReceive('foo')->withAnyArgs()->once()->ordered();
        $this->mock->foo();
        $this->mock->bar();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testEnsuresOrderingIsNotCrossMockByDefault()
    {
        $this->markTestIncomplete('Pending mock containers');
        $this->mock->shouldReceive('foo')->ordered();
        $mock2 = \Mockery::mock('bar'); // need parent container for mocks
        $mock2->shouldReceive('bar')->ordered();
        $mock2->bar();
        $this->mock->foo();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testEnsuresOrderingIsCrossMockWhenGloballyFlagSet()
    {
        $this->markTestIncomplete('Pending mock containers');
        $this->mock->shouldReceive('foo')->globally()->ordered();
        $mock2 = \Mockery::mock('bar'); // need parent container for mocks
        $mock2->shouldReceive('bar')->globally()->ordered();
        $mock2->bar();
        $this->mock->foo();
    }
    
    public function testExpectationCastToStringFormatting()
    {
        $exp = $this->mock->shouldReceive('foo')->with(1, 'bar', new stdClass, array());
        $this->assertEquals('foo(1, "bar", stdClass, Array)', (string) $exp);
    }
    
    public function testMultipleExpectationCastToStringFormatting()
    {
        $this->markTestIncomplete('Need composite expectations');
        $exp = $this->mock->shouldReceive('foo', 'bar')->with(1);
        $this->assertEquals('[foo(1), bar(1)]', (string) $exp);
    }
    
    public function testGroupedOrderingWithLimitsAllowsMultipleReturnValues()
    {
        $this->mock->shouldReceive('foo')->with(2)->once()->andReturn('first');
        $this->mock->shouldReceive('foo')->with(2)->twice()->andReturn('second/third');
        $this->mock->shouldReceive('foo')->with(2)->andReturn('infinity');
        $this->assertEquals('first', $this->mock->foo(2));
        $this->assertEquals('second/third', $this->mock->foo(2));
        $this->assertEquals('second/third', $this->mock->foo(2));
        $this->assertEquals('infinity', $this->mock->foo(2));
        $this->assertEquals('infinity', $this->mock->foo(2));
        $this->assertEquals('infinity', $this->mock->foo(2));
        $this->mock->mockery_verify();
    }
    
    public function testExpectationsCanBeMarkedAsDefaults()
    {
        $this->mock->shouldReceive('foo')->andReturn('bar')->byDefault();
        $this->assertEquals('bar', $this->mock->foo());
        $this->mock->mockery_verify();
    }
    
    public function testDefaultExpectationsValidatedInCorrectOrder()
    {
        $this->mock->shouldReceive('foo')->with(1)->once()->andReturn('first')->byDefault();
        $this->mock->shouldReceive('foo')->with(2)->once()->andReturn('second')->byDefault();
        $this->assertEquals('first', $this->mock->foo(1));
        $this->assertEquals('second', $this->mock->foo(2));
        $this->mock->mockery_verify();
    }
    
    public function testDefaultExpectationsAreReplacedByLaterConcreteExpectations()
    {
        $this->mock->shouldReceive('foo')->andReturn('bar')->once()->byDefault();
        $this->mock->shouldReceive('foo')->andReturn('bar')->twice();
        $this->mock->foo();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testDefaultExpectationsCanBeChangedByLaterExpectations()
    {
        $this->mock->shouldReceive('foo')->with(1)->andReturn('bar')->once()->byDefault();
        $this->mock->shouldReceive('foo')->with(2)->andReturn('baz')->once();
        try {
            $this->mock->foo(1);
            $this->fail('Expected exception not thrown');
        } catch (\Mockery\Exception $e) {}
        $this->mock->foo(2);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testDefaultExpectationsCanBeOrdered()
    {
        $this->mock->shouldReceive('foo')->ordered()->byDefault();
        $this->mock->shouldReceive('bar')->ordered()->byDefault();
        $this->mock->bar();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testDefaultExpectationsCanBeOrderedAndReplaced()
    {
        $this->mock->shouldReceive('foo')->ordered()->byDefault();
        $this->mock->shouldReceive('bar')->ordered()->byDefault();
        $this->mock->shouldReceive('bar')->ordered();
        $this->mock->shouldReceive('foo')->ordered();
        $this->mock->bar();
        $this->mock->foo();
        $this->mock->mockery_verify();
    }
    
    public function testByDefaultOperatesFromMockConstruction()
    {
        $container = new \Mockery\Container;
        $mock = $container->mock('f', array('foo'=>'rfoo','bar'=>'rbar','baz'=>'rbaz'))->byDefault();
        $mock->shouldReceive('foo')->andReturn('foobar');
        $this->assertEquals('foobar', $mock->foo());
        $this->assertEquals('rbar', $mock->bar());
        $this->assertEquals('rbaz', $mock->baz());
        $mock->mockery_verify();
    }
    
    public function testByDefaultOnAMockDoesSquatWithoutExpectations()
    {
        $container = new \Mockery\Container;
        $mock = $container->mock('f')->byDefault();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testByDefaultPreventedFromSettingDefaultWhenDefaultingExpectationWasReplaced()
    {
        $exp = $this->mock->shouldReceive('foo')->andReturn(1);
        $this->mock->shouldReceive('foo')->andReturn(2);
        $exp->byDefault();
    }
    
    /**
     * Argument Constraint Tests
     */
    
    public function testAnyConstraintMatchesAnyArg()
    {
        $this->mock->shouldReceive('foo')->with(1, Mockery::any())->twice();
        $this->mock->foo(1, 2);
        $this->mock->foo(1, 'str');
        $this->mock->mockery_verify();
    }
    
    public function testAnyConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::any())->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    public function testArrayConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('array'))->once();
        $this->mock->foo(array());
        $this->mock->mockery_verify();
    }
    
    public function testArrayConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('array'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testArrayConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('array'))->once();
        $this->mock->foo(1);
        $this->mock->mockery_verify();
    }
    
    public function testBoolConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('bool'))->once();
        $this->mock->foo(true);
        $this->mock->mockery_verify();
    }
    
    public function testBoolConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('bool'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testBoolConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('bool'))->once();
        $this->mock->foo(1);
        $this->mock->mockery_verify();
    }
    
    public function testCallableConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('callable'))->once();
        $this->mock->foo(function(){return 'f';});
        $this->mock->mockery_verify();
    }
    
    public function testCallableConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('callable'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testCallableConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('callable'))->once();
        $this->mock->foo(1);
        $this->mock->mockery_verify();
    }
    
    public function testDoubleConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('double'))->once();
        $this->mock->foo(2.25);
        $this->mock->mockery_verify();
    }
    
    public function testDoubleConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('double'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testDoubleConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('double'))->once();
        $this->mock->foo(1);
        $this->mock->mockery_verify();
    }
    
    public function testFloatConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('float'))->once();
        $this->mock->foo(2.25);
        $this->mock->mockery_verify();
    }
    
    public function testFloatConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('float'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testFloatConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('float'))->once();
        $this->mock->foo(1);
        $this->mock->mockery_verify();
    }
    
    public function testIntConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('int'))->once();
        $this->mock->foo(2);
        $this->mock->mockery_verify();
    }
    
    public function testIntConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('int'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testIntConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('int'))->once();
        $this->mock->foo('f');
        $this->mock->mockery_verify();
    }
    
    public function testLongConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('long'))->once();
        $this->mock->foo(2);
        $this->mock->mockery_verify();
    }
    
    public function testLongConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('long'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testLongConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('long'))->once();
        $this->mock->foo('f');
        $this->mock->mockery_verify();
    }
    
    public function testNullConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('null'))->once();
        $this->mock->foo(null);
        $this->mock->mockery_verify();
    }
    
    public function testNullConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('null'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testNullConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('null'))->once();
        $this->mock->foo('f');
        $this->mock->mockery_verify();
    }
    
    public function testNumericConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('numeric'))->once();
        $this->mock->foo('2');
        $this->mock->mockery_verify();
    }
    
    public function testNumericConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('numeric'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testNumericConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('numeric'))->once();
        $this->mock->foo('f');
        $this->mock->mockery_verify();
    }
    
    public function testObjectConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('object'))->once();
        $this->mock->foo(new stdClass);
        $this->mock->mockery_verify();
    }
    
    public function testObjectConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('object`'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testObjectConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('object'))->once();
        $this->mock->foo('f');
        $this->mock->mockery_verify();
    }
    
    public function testRealConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('real'))->once();
        $this->mock->foo(2.25);
        $this->mock->mockery_verify();
    }
    
    public function testRealConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('real'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testRealConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('real'))->once();
        $this->mock->foo('f');
        $this->mock->mockery_verify();
    }
    
    public function testResourceConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('resource'))->once();
        $r = fopen(dirname(__FILE__) . '/_files/file.txt', 'r');
        $this->mock->foo($r);
        $this->mock->mockery_verify();
    }
    
    public function testResourceConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('resource'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testResourceConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('resource'))->once();
        $this->mock->foo('f');
        $this->mock->mockery_verify();
    }
    
    public function testScalarConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('scalar'))->once();
        $this->mock->foo(2);
        $this->mock->mockery_verify();
    }
    
    public function testScalarConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('scalar'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testScalarConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('scalar'))->once();
        $this->mock->foo(array());
        $this->mock->mockery_verify();
    }
    
    public function testStringConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('string'))->once();
        $this->mock->foo('2');
        $this->mock->mockery_verify();
    }
    
    public function testStringConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('string'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testStringConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('string'))->once();
        $this->mock->foo(1);
        $this->mock->mockery_verify();
    }
    
    public function testClassConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('stdClass'))->once();
        $this->mock->foo(new stdClass);
        $this->mock->mockery_verify();
    }
    
    public function testClassConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::type('stdClass'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testClassConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::type('stdClass'))->once();
        $this->mock->foo(new Exception);
        $this->mock->mockery_verify();
    }
    
    public function testDucktypeConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::ducktype('quack', 'swim'))->once();
        $this->mock->foo(new Mockery_Duck);
        $this->mock->mockery_verify();
    }
    
    public function testDucktypeConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::ducktype('quack', 'swim'))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testDucktypeConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::ducktype('quack', 'swim'))->once();
        $this->mock->foo(new Mockery_Duck_Nonswimmer);
        $this->mock->mockery_verify();
    }
    
    public function testArrayContentConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::contains(array('a'=>1,'b'=>2)))->once();
        $this->mock->foo(array('a'=>1,'b'=>2,'c'=>3));
        $this->mock->mockery_verify();
    }
    
    public function testArrayContentConstraintNonMatchingCase()
    {
        $this->mock->shouldReceive('foo')->times(3);
        $this->mock->shouldReceive('foo')->with(1, Mockery::contains(array('a'=>1,'b'=>2)))->never();
        $this->mock->foo();
        $this->mock->foo(1);
        $this->mock->foo(1, 2, 3);
        $this->mock->mockery_verify();
    }
    
    /**
     * @expectedException \Mockery\Exception
     */
    public function testArrayContentConstraintThrowsExceptionWhenConstraintUnmatched()
    {
        $this->mock->shouldReceive('foo')->with(Mockery::contains(array('a'=>1,'b'=>2)))->once();
        $this->mock->foo(array(array('a'=>1,'c'=>3)));
        $this->mock->mockery_verify();
    }
    
}

class Mockery_Duck {
    function quack(){}
    function swim(){}
}

class Mockery_Duck_Nonswimmer {
    function quack(){}
}