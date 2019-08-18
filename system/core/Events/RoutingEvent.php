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

    public function map($path, $controller)
    {
        ErkinApp()->map($path, $controller);
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

}