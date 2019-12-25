<?php
namespace Kolesa\Clockwork;

use Clockwork\Authentication\NullAuthenticator;
use Clockwork\Authentication\SimpleAuthenticator;
use Clockwork\Clockwork;
use Clockwork\Storage\FileStorage;
use Kolesa\Clockwork\Storage\Redis;
use Phalcon\Di\Injectable;
use Phalcon\Config as PhalconConfig;


/**
 * Class Clockwork
 *
 * @property \Phalcon\Config $config
 */
class ClockworkSupport extends Injectable
{
    /**
     * Clockwork
     *
     * @var \Clockwork\Clockwork
     */
    protected $clockwork;

    /**
     * Configuration
     *
     * @var \Phalcon\Config
     */
    public $config;

    /**
     * ClockworkSupport constructor.
     */
    public function __construct(PhalconConfig $config = null)
    {
        $this->clockwork = new Clockwork();

        if (is_null($config)) {
            $config = Config::getDefault();
        }

        $this->config = $config;

        $this->clockwork->setAuthenticator($this->getAuthenticator());
        $this->clockworkStorage();
    }

    /**
     * Return auth object
     *
     * @return \Clockwork\Authentication\AuthenticatorInterface
     */
    protected function getAuthenticator()
    {
        $authenticator = $this->config->path('authentication');

        if (!empty($authenticator['enabled'])) {
            if (isset($authenticator['class'])) {
                if (class_exists($authenticator['class'])) {
                    return new $authenticator['class']($authenticator);
                } else {
                    throw new \InvalidArgumentException('Invalid authenticator class');
                }
            }

            if (isset($authenticator['password']) && $authenticator['password'] !== null) {
                return new SimpleAuthenticator($authenticator['password']);
            }
        }

        return new NullAuthenticator;
    }

    /**
     * Is clockwork enable
     */
    public function isEnable()
    {
        return $this->config->path("enable", true);
    }

    /**
     * Set storage
     *
     * @return \Clockwork\Storage\FileStorage
     * @throws \Exception
     */
    public function clockworkStorage()
    {
        $config = $this->config->path('storage');
        $type   = $config['type'];

        try {
            if ($type === 'redis') {
                $options = $config['options'] ?? [];
                $storage = new Redis($options, $config['expiration'] ?? null);
            } else {
                $storage = new FileStorage(
                    __DIR__,
                    0700,
                    $config['expiration'] ?? null
                );
            }

            $this->clockwork->setStorage($storage);
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * Return clockwork object
     *
     * @return \Clockwork\Clockwork
     */
    public function getClockwork()
    {
        return $this->clockwork;
    }

}