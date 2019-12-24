<?php
namespace Kolesa\Clockwork;

use Kolesa\Clockwork\Listeners\Application;
use Clockwork\Clockwork;
use Kolesa\Clockwork\DataSource\Phalcon;
use Phalcon\Events\Manager;
use Phalcon\Mvc\User\Component;
use Phalcon\Config as PhalconConfig;

class ClockworkServices extends Component
{
    /**
     * Default listeners
     *
     * @var array
     */
    protected $defaultListeners = [
        'application' => Application::class
    ];

    /**
     * Default data source
     *
     * @var array
     */
    protected $defaultDataSource = [
        Phalcon::class
    ];

    /**
     * Init Clockwork
     */
    public function initialize(PhalconConfig $config = null)
    {
        $clockwork = new ClockworkSupport($config);

        if (!$clockwork->isEnable()) {
            return;
        }

        if(!$this->getEventsManager()) {
            $this->setEventsManager(new Manager());
        }

        $this->di->setShared('clockwork', $clockwork);

        $this->registerRouter();
        $this->initAuth();

        $this->setListeners();
        $this->setDataSources();
    }

    /**
     * Set data source
     */
    public function setDataSources()
    {
        foreach ($this->clockwork->config->path('clockwork.dataSource', $this->defaultDataSource) as $dataSource) {
            $dataSourceObject = new $dataSource;

            if ($dataSourceObject instanceof \Phalcon\Di\InjectionAwareInterface) {
                $dataSourceObject->setDI($this->di);
            }

            $dataSourceObject->extend($this->clockwork->getClockwork()->getRequest());
            $this->clockwork->getClockwork()->addDataSource($dataSourceObject);
        }
    }

    /**
     * Register route
     */
    public function registerRouter()
    {
        $router = $this->router;
        $router->mount(new Router());

        return;
    }

    /**
     * Set headers
     */
    public function initAuth()
    {
        $this->response->setHeader('X-Clockwork-Id', 'empty');
        $this->response->setHeader('X-Clockwork-Version', Clockwork::VERSION);
    }

    /**
     * Set listeners
     */
    protected function setListeners()
    {
        $eventsManager = $this->getEventsManager();

        foreach ($this->clockwork->config->path('clockwork.listeners', $this->defaultListeners) as $event => $listener) {
            if (is_array($listener)) {
                foreach ($listener as $item) {
                    $eventsManager->attach($event, new $item);
                }

                continue;
            }

            $eventsManager->attach($event, new $listener);
        }
    }
}