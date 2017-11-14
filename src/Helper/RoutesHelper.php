<?php
declare(strict_types=1);

namespace BC\Purger\Helper;

/**
 * Class RoutesHelper
 * @package BC\Purger\Helper
 */
class RoutesHelper
{
    /**
     * @param array $routesCollection
     * @return array
     */
    public static function buildValidRoutes(array $routesCollection)
    {
        return array_map(function($route) {
            if ($route[0] !== '/') {
                $route = '/' . $route;
            }

            return $route;
        }, $routesCollection);
    }
}
