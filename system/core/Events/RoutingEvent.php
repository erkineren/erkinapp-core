<?php


namespace ErkinApp\Events;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;


class RoutingEvent extends Event
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function map($path, $controller, array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        ErkinApp()->map($path, $controller, $requirements, $options, $host, $schemes, $methods, $condition);
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

}