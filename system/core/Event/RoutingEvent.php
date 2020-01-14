<?php


namespace ErkinApp\Event;


use ErkinApp\Route\AppRoute;

class RoutingEvent extends BaseEvent
{

    public function map($path, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        ErkinApp()->AppRoutes()->add(AppRoute::fromRouteParams($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition));
    }

    public function mapController($path, $controller, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        ErkinApp()->AppRoutes()->add(AppRoute::fromRouteParams($path, array_merge(['controller' => $controller], $defaults), $requirements, $options, $host, $schemes, $methods, $condition));
    }

    public function mapControllerLanguages(array $languagePaths, $controller, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        foreach ($languagePaths as $lang => $languagePath) {
            $appRoute = AppRoute::fromRouteParams($languagePath, array_merge(['controller' => $controller], $defaults), $requirements, $options, $host, $schemes, $methods, $condition);
            $appRoute->setLang($lang);
            $appRoute->setIsAnnotationRoute(true);
            ErkinApp()->AppRoutes()->add($appRoute);
        }

    }

}