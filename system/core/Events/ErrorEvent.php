<?php

namespace ErkinApp\Events;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class ErrorEvent extends Event
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var \Throwable
     */
    protected $throwable;

    /**
     * RequestEvent constructor.
     * @param Request $request
     */
    public function __construct(Request $request, \Throwable $throwable = null)
    {

        $this->request = $request;
        $this->throwable = $throwable;
        if ($throwable != null) {
            $this->response = new Response($throwable->getTraceAsString(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function hasResponse()
    {
        return $this->response !== null;
    }

    /**
     * @return \Throwable
     */
    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }

    /**
     * @param \Throwable $throwable
     */
    public function setThrowable(\Throwable $throwable): void
    {
        $this->throwable = $throwable;
    }


}