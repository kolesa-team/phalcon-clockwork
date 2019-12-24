<?php
namespace Kolesa\Clockwork;

use Clockwork\Storage\Search;
use Clockwork\Web\Web;
use Phalcon\Mvc\Controller;

/**
 * Class ClockworkController
 */
class ClockworkController extends Controller
{
    /**
     * Auth page
     *
     * @return \Phalcon\Http\Response
     */
    public function authAction()
    {
        $token = $this->clockwork->getClockwork()->getAuthenticator()->attempt($this->request->getPost());

        $this->session->set('clockwork_token', $token);

        return $this->getJsonResponse(['token' => $token], $token ? 200 : 403);
    }

    /**
     * Data page
     *
     * @param  string|null            $id
     * @param  string|null            $direction
     * @param  int|null               $count
     * @return \Phalcon\Http\Response
     */
    public function getDataAction($id = null, $direction = null, $count = null)
    {
        $authenticator = $this->clockwork->getClockwork()->getAuthenticator();
        $storage       = $this->clockwork->getClockwork()->getStorage();
        $query         = $this->request->getQuery();
        $authenticated = $authenticator->check($this->request->getHeader('X-Clockwork-Auth'));

        if ($authenticated !== true) {
            return $this->getJsonResponse([ 'message' => $authenticated, 'requires' => $authenticator->requires() ], 403);
        }

        if ($direction == 'previous') {
            $data = $storage->previous($id, $count, Search::fromRequest($query));
        } else if ($direction == 'next') {
            $data = $storage->next($id, $count, Search::fromRequest($query));
        } else if ($direction == 'latest') {
            $data = $storage->latest($id, $count, Search::fromRequest($query));
        } else {
            $data = $storage->find($id);
        }

        $data = is_array($data)
            ? array_map(function ($request) { return $request->toArray(); }, $data)
            : $data->toArray();

        return $this->getJsonResponse($data);
    }

    /**
     * Return web asset
     *
     * @param $path
     * @return \Phalcon\Http\Response
     */
    public function getWebAssetAction($path)
    {
        return $this->getJsonResponse(['message' => 'Not support']);
    }

    /**
     * Redirect to web panel
     *
     * @return \Phalcon\Http\Response
     */
    public function webRedirectAction()
    {
        return $this->getJsonResponse(['message' => 'Not support']);
    }

    /**
     * Return json response
     *
     * @param  mixed                  $data
     * @param  int                    $code
     * @param  string                 $message
     * @return \Phalcon\Http\Response
     */
    protected function getJsonResponse($data, $code = 200, $message = '')
    {
        $this->response->setJsonContent($data);
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setStatusCode($code, $message);

        return $this->response;
    }
}
