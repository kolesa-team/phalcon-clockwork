Phalcon clockwork
=========

#Installation
Install the Clockwork library via Composer.

```shell
$ composer require kolesa-team/phalcon-clockwork
```

Set events manager to service(di)
```php

        $di->set('eventsManager', function () {
            $manager = new Phalcon\Events\Manager();

            return $manager;
        }, true);
```

Set events manager to Application
```php
    $eventsManager = $di->get('eventsManager');
    $application   = new Phalcon\Mvc\Application($di);
    
    $application->setEventsManager($eventsManager)
```

Init clockwork
```php
    $clockwork = new ClockworkServices();
    
    $clockwork->setEventsManager($di->get('eventsManager'));      
    $clockwork->initialize();
```
