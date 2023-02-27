<?php
namespace Kolesa\Clockwork;

use Phalcon\Mvc\Router\Group;
use Phalcon\Di\Di;

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

        $prefix = Di::getDefault()->get('clockwork')->config->path('api', '/__clockwork');

        $this->setPrefix($prefix);

        $this->add('/{path}', [
            'action' => 'webAsset',
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

        $this->add('', [
            'action' => 'webRedirect',
        ]);

        $this->add('/app', [
            'action' => 'webIndex',
        ]);

        $this->add('/auth', [
            'action' => 'auth',
        ]);

        $this->beforeMatch(function () {
            Di::getDefault()->getEventsManager()->detachAll();

            return true;
        });
    }
}
