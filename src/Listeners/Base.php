<?php
namespace Kolesa\Clockwork\Listeners;

use Phalcon\Di\Injectable;

/**
 * Abstract class for Listeners
 */
abstract class Base extends Injectable
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
