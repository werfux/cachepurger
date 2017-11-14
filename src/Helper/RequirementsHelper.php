<?php
declare(strict_types=1);

namespace BC\Purger\Helper;

use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Class RequirementsHelper
 * @package BC\Purger\Helper
 */
class RequirementsHelper
{
    /** @var array */
    private static $requiredExtensions = [];

    /**
     * @return bool
     * @throws \Exception
     */
    public static function checkMinimumPHPVersion()
    {
        if (PHP_VERSION_ID < 70000 ) {
            throw new RuntimeException('Minimum PHP version (7.0.x) not found.');
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function checkRequiredPHPExtensions()
    {
        foreach (self::$requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                throw new RuntimeException(sprintf('Missing required php extension "%s".', $extension));
            }
        }

        return true;
    }
}