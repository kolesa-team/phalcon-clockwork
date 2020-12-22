<?php
namespace Kolesa\Clockwork\Listeners;

use Phalcon\Mvc\User\Plugin;

/**
 * Abstract class for Listeners
 */
abstract class Base extends Plugin
{
    /**
     * Get cloсkwork
     *
     * @return \Kolesa\Clockwork\ClockworkSupport
     */
    protected function getClockwork()
    {
        return $this->di->get('clockwork')->getClockwork();
    }
}
