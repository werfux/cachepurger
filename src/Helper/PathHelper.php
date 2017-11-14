<?php

namespace BC\Purger\Helper;

use Phar;

/**
 * Class PathHelper
 * @package BC\Purger\Helper
 */
class PathHelper
{
    /**
     * @return string
     */
    public static function getRootDirectory()
    {
        return self::isPharEnvironment() ?
            self::getRootDirectoryByPharFilePath() :
            self::getRootDirectoryByCurrentDirectoryPath();
    }

    /**
     * @return bool
     */
    private static function isPharEnvironment()
    {
        $pharFilePath = Phar::running(false);

        if (empty(trim($pharFilePath))) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @return string
     */
    private static function getRootDirectoryByPharFilePath()
    {
        $pharFilePath = Phar::running(false);
        return dirname($pharFilePath);
    }

    /**
     * @return string
     */
    private static function getRootDirectoryByCurrentDirectoryPath()
    {
        return dirname(__DIR__, 2);
    }
}
