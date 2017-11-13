<?php

namespace BC\Purger\Helper;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Exception\InvalidOptionException;

/**
 * Class SourceFileHelper
 * @package BC\Purger\Helper
 */
class SourceFileHelper
{
    /**
     * @param $filePath
     * @return mixed
     */
    public static function loadDomainFromFile($filePath)
    {
        return self::loadConfigurationFromFile($filePath, 'domain', 'string');
    }

    /**
     * @param $filePath
     * @return array
     * @throws \Exception
     */
    public static function loadRoutesFromFile($filePath)
    {
        return self::loadConfigurationFromFile($filePath, 'routes', 'array');
    }

    /**
     * @param $filePath
     * @return array
     * @throws \Exception
     */
    public static function loadVarnishHostsFromFile($filePath)
    {
        return self::loadConfigurationFromFile($filePath, 'varnish_hosts', 'array');
    }

    /**
     * @param $filePath
     * @return mixed
     */
    public static function loadRedisConnectionsFromFile($filePath)
    {
        return self::loadConfigurationFromFile($filePath, 'redis_connections', 'array');
    }

    /**
     * @param $filePath
     * @param $propertyName
     * @param $propertyType
     * @return mixed
     * @throws \Exception
     */
    private static function loadConfigurationFromFile($filePath, $propertyName, $propertyType)
    {
        $sourceFile = Yaml::parse(file_get_contents($filePath));

        if (!array_key_exists($propertyName, $sourceFile)) {
            throw new InvalidOptionException(sprintf('Please ensure that the source yaml has a property named "%s".', $propertyName));
        }

        if ($propertyType === 'array' && !is_array($sourceFile[$propertyName])) {
            throw new InvalidOptionException(sprintf('The "%s" proptery in the yaml file has to be an array.', $propertyName));
        }

        if ($propertyType === 'string' && !is_string($sourceFile[$propertyName])) {
            throw new InvalidOptionException(sprintf('The "%s" proptery in the yaml file has to be a string.', $propertyName));
        }

        return $sourceFile[$propertyName];

    }
}
