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
     * @var \Exception
     */
    protected $exception;

    /**
     * RequestEvent constructor.
     * @param Request $request
     */
    public function __construct(Request $request, \Exception $exception = null)
    {
        $this->request = $request;
        $this->exception = $exception;
        if ($exception != null) {
            $this->response = new Response($exception->getTraceAsString(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
     * @return \Exception
     */
    public function getException(): \Exception
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException(\Exception $exception): void
    {
        $this->exception = $exception;
    }


}