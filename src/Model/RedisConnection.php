<?php
declare(strict_types=1);

namespace BC\Purger\Model;

/**
 * Class RedisConnection
 * @package BC\Purger\Model
 */
class RedisConnection
{
    /** @var string */
    private $scheme = 'tcp';

    /** @var string  */
    private $host = 'localhost';

    /** @var int */
    private $port = 6379;

    /** @var string */
    private $password;

    /**
     * RedisConnection constructor.
     * @param array $connectionData
     */
    public function __construct(array $connectionData)
    {
        if (array_key_exists('host', $connectionData) && is_string($connectionData['host'])) {
            $this->host = $connectionData['host'];
        }

        if (array_key_exists('port', $connectionData) && is_numeric($connectionData['port'])) {
            $this->port = (int) $connectionData['port'];
        }

        if (array_key_exists('password', $connectionData) && is_string($connectionData['password'])) {
            $this->password = $connectionData['password'];
        }
    }

    /**
     * @return mixed
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return bool
     */
    public function hasPassword()
    {
        return (null !== $this->password);
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

}
