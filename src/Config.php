<?php
namespace Kolesa\Clockwork;

use Clockwork\DataSource\PhpDataSource;
use Kolesa\Clockwork\DataSource\Phalcon;
use Phalcon\Config\Config as PhalconConfig;
use Phalcon\Di\Injectable;

/**
 * Configuration
 */
class Config extends Injectable
{
    /**
     * Filter black list
     *
     * @var array|null
     */
    protected $filterBlackList = null;

    /**
     * Filter white list
     *
     * @var array|null
     */
    protected $filterWhiteList = null;

    /**
     * Is hide class name
     *
     * @var bool|null
     */
    protected $isHideClasses = null;

    /**
     * Is hide private property
     *
     * @var bool|null
     */
    protected $hideUnpublic = null;

    /**
     * Return default config
     *
     * @return \Phalcon\Config
     */
    public static function getDefault()
    {
        return new PhalconConfig(
            [
                'clockwork' => [
                    'enabled'        => false,
                    'path'           => '/__clockwork',
                    'storage'        => [
                        'expiration' => 10,
                    ],
                    'authentication' => [
                        'enabled' => false,
                    ],
                    'dataSource'     => [
                        PhpDataSource::class,
                        Phalcon::class,
                    ],
                    'listeners'      => [
                        'application' => Listeners\Application::class,
                    ],
                    'serializer'     => [],
                    'filter'         => [
                        'blackList'     => [
                            'pass',
                        ],
                        'whiteList'     => [],
                        'hideClasses'   => false,
                        'hideNotPublic' => true,
                    ],
                ]
            ]
        );
    }


    /**
     * Return blacklist for filter
     *
     * @return string[]
     */
    public function getFilterBlackList()
    {
        if (is_null($this->filterBlackList)) {
            $filter                = $this->config->path('filter', [])->toArray();
            $this->filterBlackList = $filter['blackList'] ?? [];
        }

        return $this->filterBlackList;
    }

    /**
     * Return whitelist for filter
     *
     * @return string[]
     */
    public function getFilterWhiteList()
    {
        if (is_null($this->filterWhiteList)) {
            $filter                = $this->config->path('filter', [])->toArray();
            $this->filterWhiteList = $filter['whiteList'] ?? [];
        }

        return $this->filterWhiteList;
    }

    /**
     * @see \Kolesa\Clockwork\Config::$isHideClasses
     *
     * @return bool
     */
    public function isHideClasses()
    {
        if (is_null($this->isHideClasses)) {
            $filter              = $this->config->path('filter', [])->toArray();
            $this->isHideClasses = $filter['hideClasses'] ?? false;
        }

        return $this->isHideClasses;
    }

    /**
     * @see \Kolesa\Clockwork\Config::$isHideNotPublic
     *
     * @return bool
     */
    public function isHideNotPublic()
    {
        if (is_null($this->hideUnpublic)) {
            $filter             = $this->config->path('filter', [])->toArray();
            $this->hideUnpublic = $filter['hideNotPublic'] ?? false;
        }

        return $this->hideUnpublic;
    }
}
