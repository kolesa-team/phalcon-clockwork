<?php
namespace Kolesa\Clockwork\Listeners;

use Phalcon\Db\Profiler;
use Phalcon\Events\Event;

/**
 * Listener for DB
 */
class DataBase extends Base
{
    protected $profiler;

    public function __construct()
    {
        $this->profiler = new Profiler();
    }

    /**
     * This is executed if the event triggered is 'beforeQuery'
     *
     * @param \Phalcon\Events\Event $event
     * @param \Phalcon\Db\Adapter   $connection
     */
    public function beforeQuery(Event $event, $connection)
    {
        $this->getProfiler()->startProfile(
            $connection->getSQLStatement()
        );
    }

    /**
     * @param \Phalcon\Events\Event $event
     * @param \Phalcon\Db\Adapter   $connection
     */
    public function afterQuery(Event $event, $connection)
    {
        $profile = $this->getProfiler()->stopProfile()->getLastProfile();
        $this->addQuery($profile, $connection);
    }

    /**
     * @return \Phalcon\Db\Profiler
     */
    public function getProfiler(): Profiler
    {
        return $this->profiler;
    }

    /**
     * @param \Phalcon\Db\Profiler\Item $profile
     * @param \Phalcon\Db\Adapter       $connection
     */
    protected function addQuery($profile, $connection)
    {
        $this->getClockwork()->getRequest()->addDatabaseQuery(
            $profile->getSQLStatement(),
            $connection->getSqlVariables(),
            $profile->getTotalElapsedSeconds() * 1000
        );
    }
}
