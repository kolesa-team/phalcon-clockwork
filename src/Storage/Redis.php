<?php namespace Kolesa\Clockwork\Storage;

use Clockwork\Request\Request;
use Clockwork\Storage\Search;
use Clockwork\Storage\Storage;

/**
 * Redis data storage
 */
class Redis extends Storage
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
     * Redis client
     *
     * @var \Redis
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
        $this->options    = $options;

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
        return $this->idsToRequests([$id])[0] ?? new Request();
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

        return $this->find(end($ids));
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
        $lastIndex  = array_search($id, $ids);
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
        $firstIndex = array_search($id, $ids) + 1;
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
        $this->getClient()->hSet(
            self::KEY_HASH_STORED,
            $request->id,
            @json_encode($request->toArray(), \JSON_PARTIAL_OUTPUT_ON_ERROR)
        );

        if ($this->expiration > 0) {
            $this->getClient()->expire(self::KEY_HASH_STORED, $this->expiration);
        }

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
     * @return Redis
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
        return $this->getClient()->hKeys(self::KEY_HASH_STORED);
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
        $data   = $this->getClient()->hMGet(self::KEY_HASH_STORED, $ids);

        foreach ($data as $value) {
            $result[] = new Request(json_decode($value, true));
        }

        return $result;
    }

    /**
     * Connect to redis
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

        $redis = new \Redis();

        if (!$redis->connect($host, $port, $timeout)) {
            throw new \Exception('Could not connect to the Redisd server ' . $host . ':' . $port);
        }

        $auth = $options['auth'] ?? null;

        if ($auth) {
            $redis->auth($auth);
            if ($redis->auth($auth)) {
                throw new \Exception('Failed to authenticate with the Redisd server');
            }
        }

        $this->client = $redis;
    }

    /**
     * @see \Kolesa\Clockwork\Storage\Redis::$client
     *
     * @return \Redis
     * @throws \Exception
     */
    protected function getClient()
    {
        $client = $this->client;

        if (!$client instanceof \Redis) {
            $this->connect();

            $client = $this->client;
        }

        return $client;
    }
}
