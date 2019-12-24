<?php
namespace Kolesa\Clockwork;

use Phalcon\Mvc\Router\Group;
use Phalcon\Di;

/**
 * Router
 */
class Router extends Group
{
    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->setPaths([
            'namespace'  => 'Kolesa\Clockwork',
            'controller' => 'clockwork',
        ]);

        $prefix = Di::getDefault()->get('clockwork')->config->path('clockwork.api', '/__clockwork');

        $this->setPrefix($prefix);

        $this->add('', [
            'action' => 'webRedirect',
        ]);

        $this->add('/app', [
            'action' => 'webIndex',
        ]);

        $this->add('/{path}', [
            'action' => 'webAsset',
        ]);

        $this->add('/auth', [
            'action' => 'authenticate',
        ]);

        $this->add('/{id}', [
            'action' => 'getData',
        ]);

        $this->add('/{id}/{direction}', [
            'action' => 'getData',
        ]);

        $this->add('/{id}/{direction}/{count}', [
            'action' => 'getData',
        ]);

        $this->beforeMatch(function () {
            Di::getDefault()->getEventsManager()->detachAll();

            return true;
        });
    }
}
