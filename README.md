# Web2All timer

This package can be used to run code at a specific interval. This is mostly useful for daemon-like scripts.

## What does it do ##

The main goal of the package (class) is to sleep for a specific amount of time. The sleep time is calculated so work can be done at fixed intervals. This is useful for long running scripts, as an alternative to running them each interval from teh cron.

## Usage ##

The below code will 'do stuff' every minute. If the 'do stuff' takes long, the sleep will sleep as long as needed till the next interval moment which has not yet been reached. When it is one second before midnight, the script will exit the work loop. 

So if this script is started at 00:00:05 then the next interval will be 00:01:05. If the 'do stuff' takes 70 seconds then the second time 'do work' will be done is at 00:02:05, the sleep will sleep for 50 seconds.

    $interval_timer = new Web2All_Timer_SleepInterval(
        60, 
        Web2All_Timer_SleepInterval::OPTION_NO_INTERVAL_DRIFT
    );
    $interval_timer->initialize();
    $interval_timer->setEndAtMidnight();
    $keep_running = true;
    while($keep_running){
      // do stuff
      
      // wait till the next interval
      $keep_running = $interval_timer->sleep();
    }

But there are options to control the exact behaviour. See code ducumentation for the constructor, `setEndAtMidnight` and `initialize` method.

## License ##

Web2All framework is open-sourced software licensed under the MIT license ([https://opensource.org/licenses/MIT](https://opensource.org/licenses/MIT "license")).
