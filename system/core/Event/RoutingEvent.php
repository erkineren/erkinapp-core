<?php


namespace ErkinApp\Event;


class RoutingEvent extends BaseEvent
{

    public function map($path, $controller, array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        ErkinApp()->map($path, $controller, $requirements, $options, $host, $schemes, $methods, $condition);
    }

}