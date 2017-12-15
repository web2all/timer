<?php

/**
 * Web2All Timer SleepInterval class
 *
 * Sleep for given interval.
 *
 * @author Merijn van den Kroonenberg
 * @copyright (c) Copyright 2017 Web2All BV
 * @since 2017-08-23
 */
class Web2All_Timer_SleepInterval {
  
  /*
   * OPTION_NO_INTERVAL_DRIFT: each interval will aim to start at the same 
   * second as the first one. This prevents drift of the  sleep cycle, 
   * making it start at a boundary of $interval_seconds (+- one second). 
   * Type: bitmask
   */
  const OPTION_NO_INTERVAL_DRIFT = 1;
  /*
   * OPTION_NEVER_SKIP_INTERVAL: each interval will always be exact
   * $interval_seconds after the last one. This makes a difference when
   * program execution can exceed the interval time. By default 'missed'
   * intervals will be skipped: the timer won't sleep and return 
   * immediately, but the next run it will sleep again.
   * If you enable this option, the timer will not sleep, possibly many 
   * times, till it caught up again.
   * Implies OPTION_NO_INTERVAL_DRIFT
   * Type: bitmask
   */
  const OPTION_NEVER_SKIP_INTERVAL = 3;
  
  /**
   * Option bitmask
   *
   * @var int
   */
  protected $options;
  
  /**
   * The interval time in seconds
   *
   * @var int
   */
  protected $interval_seconds;
  
  /**
   * Timestamp of last run
   *
   * @var int
   */
  protected $last_run_time;
  
  /**
   * When do we need to exit
   *
   * @var int
   */
  protected $end_time_stamp;
  
  /**
   * Did we reach our end-time
   *
   * @var boolean
   */
  protected $end_time_reached;
  
  /**
   * constructor
   * 
   * @param int $interval_seconds
   * @param int $options  option bitmask
   */
  public function __construct($interval_seconds = 60, $options = 0) 
  {
    $this->end_time_reached = false;
    
    $this->interval_seconds = $interval_seconds;
    $this->options = $options;
  }
  
  /**
   * Set the interval time in seconds
   * 
   * @param int $interval_seconds
   */
  public function setIntervalSeconds($interval_seconds)
  {
    $this->interval_seconds = $interval_seconds;
  }
  
  /**
   * Set end time to the last second of the day
   * 
   * This is useful for cycling long running scripts once a day
   * @param int $margin_seconds  Optional, amount of seconds extra margin before midnight
   */
  public function setEndAtMidnight($margin_seconds = 0)
  {
    $end_time = new DateTime();
    
    // end_time is set to last second of the day
    $end_time->setTime(23,59,59);
    
    $this->end_time_stamp = $end_time->getTimestamp() - $margin_seconds;
  }
  
  /**
   * Set end time to the given unix timestamp
   * 
   * @param int $unix_timestamp
   */
  public function setEndTimestamp($unix_timestamp)
  {
    $this->end_time_stamp = $unix_timestamp;
  }
  
  /**
   * Initialize interval timer
   * 
   * @param boolean $end_at_midnight
   * @param integer $fake_start_time  Set this to force the "start" moment of the timer
   */
  public function initialize($end_at_midnight = false, $fake_start_time = null)
  {
    if($end_at_midnight){
      $this->setEndAtMidnight();
    }
    if($fake_start_time){
      $this->last_run_time = $fake_start_time;
    }else{
      $this->last_run_time = time();
    }
  }
  
  /**
   * Sleep till next interval
   * 
   * @return boolean  returns false when end time is reached
   */
  public function sleep()
  {
    // check exit condition and wait a bit
    $now = time();
    $next_interval = $this->last_run_time + $this->interval_seconds;
    // calculate how long to wait till next interval
    $sleep_time = $next_interval - $now;
    // if the time is negative, we already passed our next interval time (we missed it)
    if($sleep_time < 0){
      // action depends on options
      if(($this->options & self::OPTION_NO_INTERVAL_DRIFT) && !($this->options & self::OPTION_NEVER_SKIP_INTERVAL)){
        // OPTION_NO_INTERVAL_DRIFT is set, but not OPTION_NEVER_SKIP_INTERVAL
        // this means we need to look for next intervals, till it is in the future
        while($sleep_time < 0){
          $next_interval = $next_interval + $this->interval_seconds;
          $sleep_time = $next_interval - $now;
        }
      }else{
        // don't need to sleep
        $sleep_time = 0;
      }
    }
    
    // check exit condition (end time)
    if($this->end_time_stamp){
      if($this->end_time_stamp < $now){
        // we already exceeded our end time
        $this->end_time_reached = true;
        // don't need to wait
        return !$this->end_time_reached;
      }
      if($this->end_time_stamp < $next_interval){
        // okay, next interval would be after our end time, which means we are basically done
        $this->end_time_reached = true;
        $sleep_end = $this->end_time_stamp - $now;
        if($sleep_end < $sleep_time){
          // shorten sleep time till the end time
          $sleep_time = $sleep_end;
        }
      }
    }
    
    // now do the actual sleeping
    if($sleep_time){
      sleep($sleep_time);
    }
    
    if($this->options & self::OPTION_NO_INTERVAL_DRIFT){
      $this->last_run_time = $next_interval;
    }else{
      $this->last_run_time = time();
    }
    
    return !$this->end_time_reached;
  }
  
  /**
   * Get the current interval unix timestamp as used by the timer
   * 
   * @return int
   */
  public function getIntervalTimestamp()
  {
    return $this->last_run_time;
  }
  
}

?>