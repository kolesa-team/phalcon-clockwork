<?php namespace Kolesa\Clockwork\Storage;

use Clockwork\Request\Request;
use Clockwork\Storage\Search;
use Clockwork\Storage\Storage;

/**
 * Memcached data storage
 */
class Memcached extends Storage
{
    /**
     * Default expiration time
     *
     * @const int
     */
    const DEFAULT_EXPIRATION_TIME = 60;

    /**
     * Stored key
     *
     * @const string
     */
    const KEY_HASH_STORED = 'Clockwork:Storage';

    /**
     * Memcached client
     *
     * @var \Memcached
     */
    protected $client;

    /**
     * Expiration time
     *
     * @var int
     */
    protected $expiration = self::DEFAULT_EXPIRATION_TIME;

    /**
     * Constructor
     *
     * @param array    $options
     * @param null|int $expiration
     */
    public function __construct($options, $expiration = null)
    {
        $this->options = $options;

        if ($expiration !== null) {
            $this->expiration = $expiration;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return \Clockwork\Request\Request[]
     * @throws \Exception
     */
    public function all(Search $search = null)
    {
        return $this->idsToRequests($this->ids());
    }

    /**
     * {@inheritdoc}
     *
     * @param  int                        $id
     * @return \Clockwork\Request\Request
     * @throws \Exception
     */
    public function find($id)
    {
        return $this->idsToRequests([$this->getKey($id)])[0] ?? new Request();
    }

    /**
     * {@inheritdoc}
     *
     * @return \Clockwork\Request\Request
     * @throws \Exception
     */
    public function latest(Search $search = null)
    {
        $ids = $this->ids();

        return $this->idsToRequests([end($ids)])[0] ?? new Request();
    }

    /**
     * {@inheritdoc}
     *
     * @param  string                       $id
     * @param  int|null                     $count
     * @return \Clockwork\Request\Request[]
     * @throws \Exception
     */
    public function previous($id, $count = null, Search $search = null)
    {
        $ids        = $this->ids();
        $lastIndex  = array_search($this->getKey($id), $ids);
        $firstIndex = $count && $lastIndex - $count > 0 ? $lastIndex - $count : 0;

        return $this->idsToRequests(array_slice($ids, $firstIndex, $lastIndex - $firstIndex));
    }

    /**
     * {@inheritdoc}
     *
     * @param  string                       $id
     * @param  int|null                     $count
     * @return \Clockwork\Request\Request[]
     * @throws \Exception
     */
    public function next($id, $count = null, Search $search = null)
    {
        $ids        = $this->ids();
        $firstIndex = array_search($this->getKey($id), $ids) + 1;
        $lastIndex  = $count && $firstIndex + $count < count($ids) ? $firstIndex + $count : count($ids);

        return $this->idsToRequests(array_slice($ids, $firstIndex, $lastIndex - $firstIndex));
    }

    /**
     * {@inheritdoc}
     *
     * @param  \Clockwork\Request\Request $request
     * @throws \Exception
     */
    public function store(Request $request)
    {


        $this->getClient()->add(self::KEY_HASH_STORED, $this->getKey($request->id), $this->expiration);
        $this->getClient()->set(
            $this->getKey($request->id),
            @json_encode($request->toArray(), \JSON_PARTIAL_OUTPUT_ON_ERROR),
            $this->expiration
        );

        $this->cleanup();
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup()
    {
    }

    /**
     * Устанавливает время жизни
     *
     * @param  int   $expiration
     * @return Memcached
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * Return ids
     *
     * @return array
     * @throws \Exception
     */
    protected function ids()
    {
        return $this->getClient()->get(self::KEY_HASH_STORED);
    }

    /**
     * Return data by ids
     *
     * @param  array                        $ids
     * @return \Clockwork\Request\Request[]
     * @throws \Exception
     */
    protected function idsToRequests($ids)
    {
        $result = [];
        $data   = $this->getClient()->getMulti($ids);

        foreach ($data as $value) {
            $result[] = new Request(json_decode($value, true));
        }

        return $result;
    }

    /**
     * Connect to Memcached
     *
     * @throws \Exception
     */
    protected function connect()
    {
        $options = $this->options;
        $host    = $options['host'] ?? null;
        $port    = $options['port'] ?? null;
        $timeout = $options['timeout'] ?? null;

        if ($host === null) {
            throw new \Exception('Unexpected inconsistency in options');
        }

        $client = new \Memcached();

        if (!$client->addServer($host, $port, $timeout)) {
            throw new \Exception('Could not connect to the Memcached server ' . $host . ':' . $port);
        }

        $auth = $options['auth'] ?? null;

        if ($auth) {
            $client->auth($auth);
            if ($client->auth($auth)) {
                throw new \Exception('Failed to authenticate with the Memcached server');
            }
        }

        $this->client = $client;
    }

    /**
     * @see \Kolesa\Clockwork\Storage\Memcached::$client
     *
     * @return \Memcached
     * @throws \Exception
     */
    protected function getClient()
    {
        $client = $this->client;

        if (!$client instanceof \Memcached) {
            $this->connect();

            $client = $this->client;
        }

        return $client;
    }


    /**
     * Get key by storage
     *
     * @param  string $id
     * @return string
     */
    protected function getKey($id)
    {
        return sprintf('%s-%s', self::KEY_HASH_STORED, $id);
    }
}
