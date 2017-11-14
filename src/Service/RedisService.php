<?php
declare(strict_types=1);

namespace BC\Purger\Service;

use BC\Purger\Model\RedisConnection;
use Predis\Client;

/**
 * Class RedisService
 * @package BC\Purger\Service
 */
class RedisService
{
    /** @var Client */
    private $client;

    /**
     * RedisService constructor.
     * @param RedisConnection $redisConnection
     */
    public function __construct(RedisConnection $redisConnection)
    {
        $this->client = new Client([
            'scheme' => $redisConnection->getScheme(),
            'host' => $redisConnection->getHost(),
            'port' => $redisConnection->getPort(),
        ]);

        if ($redisConnection->hasPassword()) {
            $this->client->set('password', $redisConnection->getPassword());
        }
    }

    /**
     * @return void
     */
    public function flushAll()
    {
        $this->client->flushall();
    }

    /**
     * @param $redisDatabase
     */
    public function flushDatabase($redisDatabase)
    {
        $this->client->set('database', $redisDatabase);
        $this->client->flushdb();
    }

    /**
     * @param array $redisKeys
     */
    public function flushKeys(array $redisKeys)
    {
        if (!empty($redisKeys)) {
            $this->client->del($redisKeys);
        }
    }

    /**
     * @param $redisPattern
     */
    public function flushKeysByPattern($redisPattern)
    {
        $keys = $this->client->keys($redisPattern);
        $this->flushKeys($keys);
    }
}
