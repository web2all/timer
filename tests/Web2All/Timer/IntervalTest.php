<?php
use PHPUnit\Framework\TestCase;

class Web2All_Timer_IntervalTest extends TestCase
{
  /**
   * Test Interval class
   * 
   */
  public function testInterval()
  {
    $interval = new Web2All_Timer_SleepInterval(1);
    $interval->initialize();
    $time = time();
    $res = $interval->sleep();
    $this->assertEquals($time + 1, time(), 'expect one second to have passed');
    $this->assertEquals(true, $res, 'expect true as no end time is set');
  }

  /**
   * Test Interval end time
   * 
   */
  public function testEndTime()
  {
    $interval = new Web2All_Timer_SleepInterval(2);
    $interval->setEndTimestamp(time() + 1);
    $interval->initialize();
    $time = time();
    $res = $interval->sleep();
    $this->assertEquals($time + 1, time(), 'expect one second to have passed');
    $this->assertEquals(false, $res, 'expect false as end time should have been reached');
  }

  /**
   * Test Interval end time
   * 
   */
  public function testEndTimeInPast()
  {
    $interval = new Web2All_Timer_SleepInterval(1);
    $interval->setEndTimestamp(time() - 1);
    $interval->initialize();
    $utime = microtime(true);
    $res = $interval->sleep();
    $timediff = microtime(true) - $utime;
    $this->assertTrue($timediff < 0.1, 'expect no time to have passed but '.$timediff.'s has');// less than 0.1 secs should have passed
    $this->assertEquals(false, $res, 'expect false as end time should have been reached');
    $res = $interval->sleep();
    $timediff = microtime(true) - $utime;
    $this->assertTrue($timediff < 0.1, 'expect no time to have passed but '.$timediff.'s has');// less than 0.1 secs should have passed
    $this->assertEquals(false, $res, 'expect false as end time should have been reached');
  }

  /**
   * Test Interval OPTION_NO_INTERVAL_DRIFT
   * 
   */
  public function testIntervalNoDrift()
  {
    $interval = new Web2All_Timer_SleepInterval(1,Web2All_Timer_SleepInterval::OPTION_NO_INTERVAL_DRIFT);
    $interval->initialize();
    $utime = microtime(true);
    $res = $interval->sleep();
    $timediff = microtime(true) - $utime;
    $this->assertTrue(($timediff < 1.01 && $timediff > 0.99), 'expect about a second to have passed but '.$timediff.'s has');
    $this->assertEquals(true, $res, 'expect true as no end time is set');
    
    $res = $interval->sleep();
    $timediff = microtime(true) - $utime;
    $this->assertTrue(($timediff < 2.01 && $timediff > 1.99), 'expect about two seconds to have passed but '.$timediff.'s has');
    $this->assertEquals(true, $res, 'expect true as no end time is set');
  }

  /**
   * Test Interval OPTION_NEVER_SKIP_INTERVAL
   * 
   */
  public function testIntervalNoSkip()
  {
    $interval = new Web2All_Timer_SleepInterval(1,Web2All_Timer_SleepInterval::OPTION_NEVER_SKIP_INTERVAL);
    $interval->initialize();
    $utime = microtime(true);
    $res = $interval->sleep();
    $timediff = microtime(true) - $utime;
    $this->assertTrue(($timediff < 1.01 && $timediff > 0.99), 'expect about a second to have passed but '.$timediff.'s has');
    $this->assertEquals(true, $res, 'expect true as no end time is set');
    
    $res = $interval->sleep();
    $timediff = microtime(true) - $utime;
    $this->assertTrue(($timediff < 2.01 && $timediff > 1.99), 'expect about two seconds to have passed but '.$timediff.'s has');
    $this->assertEquals(true, $res, 'expect true as no end time is set');
  }

  /**
   * Test Interval initialize in the past
   * 
   * Interval in the past means the timer has to wake up for every missed
   * interval. And it should not actually sleep inbetween until it catched
   * up with the current time (when using OPTION_NEVER_SKIP_INTERVAL).
   */
  public function testIntervalFakeStartPast()
  {
    $interval = new Web2All_Timer_SleepInterval(1,Web2All_Timer_SleepInterval::OPTION_NEVER_SKIP_INTERVAL);
    $interval->initialize(false, time()-2);
    $utime = microtime(true);
    $res = $interval->sleep();
    $timediff = microtime(true) - $utime;
    $this->assertTrue(($timediff < 0.01), 'expect no time to have passed but '.$timediff.'s has');
    $this->assertEquals(true, $res, 'expect true as no end time is set');
    
    $res = $interval->sleep();
    $timediff = microtime(true) - $utime;
    $this->assertTrue(($timediff < 0.01), 'expect no time to have passed but '.$timediff.'s has');
    $this->assertEquals(true, $res, 'expect true as no end time is set');
    
    $res = $interval->sleep();
    $timediff = microtime(true) - $utime;
    $this->assertTrue(($timediff < 1.01 && $timediff > 0.99), 'expect about one second to have passed but '.$timediff.'s has');
    $this->assertEquals(true, $res, 'expect true as no end time is set');
  }

  /**
   * Test Interval initialize in the future
   * 
   * Interval in the future means the timer has to sleep until its actually
   *  scheduled to start. And then it should start doing its normal interval stuff.
   */
  public function testIntervalFakeStartFuture()
  {
    $interval = new Web2All_Timer_SleepInterval(1,Web2All_Timer_SleepInterval::OPTION_NEVER_SKIP_INTERVAL);
    $interval->initialize(false, time()+1);
    $utime = microtime(true);
    $res = $interval->sleep();
    $timediff = microtime(true) - $utime;
    $this->assertTrue(($timediff < 2.01 && $timediff > 1.99), 'expect about two seconds to have passed but '.$timediff.'s has');
    $this->assertEquals(true, $res, 'expect true as no end time is set');
  }

}
?>