<?php
namespace Kolesa\Clockwork\Listeners;

use Clockwork\Clockwork;
use Clockwork\Helpers\ServerTiming;
use Phalcon\Mvc\User\Plugin;

/**
 * Listener for Application
 */
class Application extends Base
{
    /**
     * Before send response event
     */
    public function beforeSendResponse()
    {
        $clockwork = $this->getClockwork();
        $clockwork->timeline()->finalize();
        $clockwork->resolveRequest()->storeRequest();

        $this->response->setHeader('X-Clockwork-Id', $clockwork->getRequest()->id);
        $this->response->setHeader('X-Clockwork-Version', Clockwork::VERSION);
        $this->response->setHeader(
            'X-Clockwork-Path',
            "{$this->di->get('clockwork')->config->path('api', '/__clockwork')}/"
        );
        $this->response->setHeader('Server-Timing', ServerTiming::fromRequest($clockwork->getRequest())->value());
    }
}
