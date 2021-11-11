<?php
namespace Kolesa\Clockwork\DataSource;

use Clockwork\DataSource\DataSource;
use Clockwork\Helpers\Serializer;
use Kolesa\Clockwork\Config;
use Phalcon\Di\InjectionAwareInterface;

/**
 * Abstract class for DataSource
 */
abstract class Base  extends DataSource implements InjectionAwareInterface
{
    /**
     * DI
     *
     * @var \Phalcon\DI
     */
    protected $di = null;

    /**
     * {@inheritdoc}
     *
     * @param  \Phalcon\DI $dependencyInjector
     * @return \Object
     */
    public function setDI(\Phalcon\Di\DiInterface $container): void
    {
        $this->di = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Di\DiInterface
     */
    public function getDI(): \Phalcon\Di\DiInterface
    {
        if (null === $this->di) {
            $this->di = \Phalcon\Di\FactoryDefault::getDefault();
        }

        return $this->di;
    }

    /**
     * Get cloсkwork object
     *
     * @return \Kolesa\Clockwork\ClockworkServices
     */
    public function getClockwork()
    {
        return $this->getDI()->get('clockwork')->getClockwork();
    }

    /**
     * Get configuration
     *
     * @return \Kolesa\Clockwork\Config
     */
    public function getConfig()
    {
        return new Config();
    }

    /**
     * Filter data
     *
     * @param  array $data
     * @return array
     */
    public function filterData(array $data)
    {
        $config = $this->getConfig();

        foreach ($data as $key => &$value) {
            if ($config->isHideNotPublic() && $this->isNotPublic($key)) {
                unset($data[$key]);
            } elseif ($this->isRequiredToHide($key)) {
                $value = '*removed*';
            } elseif (is_array($value) &&
                $config->isHideClasses() &&
                ($value['__class__'] ?? false)) {
                $value = '*removed*';
            } elseif (is_array($value)) {
                $value = $this->filterData($value);
            }
        }

        return $data;
    }

    /**
     * Normalize data
     *
     * @param  array $data
     * @return array
     */
    protected function normalize($data)
    {
        $result = [];

        foreach ($data as $key => $item) {
            if (is_array($item) & !method_exists($item, 'toArray')) {
                $item = $this->normalize($item);
            } elseif (method_exists($item, 'toArray')) {
                try {
                    $item = $item->toArray();
                } catch (\Exception $e) {
                    $item = get_class($item);
                }
            }

            $result[$key] = $item;
        }

        return $result;
    }

    /**
     * Is hide property
     *
     * @param  string $name
     * @return bool
     */
    protected function isRequiredToHide($name)
    {
        $config = $this->getConfig();

        if (!in_array($name, $config->getFilterWhiteList(), true)) {
            foreach ($config->getFilterBlackList() as $word) {
                if (false !== stripos($name, $word)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Is not public property
     *
     * @param  string $name
     * @return bool
     */
    protected function isNotPublic($name)
    {
        return in_array(substr($name, 0, 1), ['~', '*'], true);
    }

    /**
     * Return serializer
     *
     * @return \Clockwork\Helpers\Serializer
     */
    protected function getSerializer()
    {
        return new Serializer($this->getDi()->get('clockwork')->config->path('serializer', [])->toArray());
    }
}
