<?php
namespace Kolesa\Clockwork\DataSource;

use Clockwork\Request\Request;
use Phalcon\Di;

/**
 * DataSource for phalcon
 */
class Phalcon extends Base
{
    /**
     * {@inheritdoc}
     *
     * @param  \Clockwork\Request\Request $request
     * @return \Clockwork\Request\Request
     */
    public function resolve(Request $request)
    {
        $request->method      = $this->getDI()->getRequest()->getMethod();
        $request->uri         = $this->getDI()->getRequest()->getURI();
        $request->controller  = $this->getDI()->getRouter()->getControllerName();
        $request->headers     = $this->getDI()->getRequest()->getHeaders();

        return $request;
    }
}
