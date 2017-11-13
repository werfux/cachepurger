<?php

namespace BC\Purger\Helper;


use BC\Purger\Exception\MalformedCredentialsException;
use BC\Purger\Exception\MissingFileException;

/**
 * Class AkamaiHelper
 * @package BC\Purger\Helper
 */
class AkamaiHelper
{
    /**
     * @return bool
     * @throws MalformedCredentialsException
     * @throws MissingFileException
     */
    public static function validEdgeRcFileExists()
    {
        $edgeRcFile = getcwd() . '/.edgerc';

        if (!file_exists($edgeRcFile)) {
            return FALSE;
        }

        if (!self::validateEdgeRcFile($edgeRcFile)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param $edgeRcFile
     * @return bool
     */
    private static function validateEdgeRcFile($edgeRcFile)
    {
        $edgeRcFileContent = parse_ini_file(getcwd() . '/.edgerc');

        if (empty($edgeRcFileContent)) {
            return false;
        }

        if (!self::validateRequiredKeys($edgeRcFileContent)) {
            return false;
        }

        foreach ($edgeRcFileContent as $item) {
            if (empty($item)) {
                return false;
            }

            if (!is_string($item)) {
                return false;
            }

            if (strlen($item) < 30) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $edgeRcFileContent
     * @return bool
     */
    private static function validateRequiredKeys($edgeRcFileContent) {
        if (!array_key_exists('client_secret', $edgeRcFileContent)) {
            return false;
        }

        if (!array_key_exists('host', $edgeRcFileContent)) {
            return false;
        }

        if (!array_key_exists('access_token', $edgeRcFileContent)) {
            return false;
        }

        if (!array_key_exists('client_token', $edgeRcFileContent)) {
            return false;
        }

        return true;
    }
}
