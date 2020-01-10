<?php

namespace ErkinApp\Event;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ErrorEvent extends BaseEvent
{

    /**
     * @var Throwable
     */
    protected $throwable;

    /**
     * RequestEvent constructor.
     * @param Request $request
     * @param Throwable|null $throwable
     */
    public function __construct(Request $request, Throwable $throwable = null)
    {
        parent::__construct($request);
        $this->request = $request;
        $this->throwable = $throwable;
        if ($throwable != null) {
            $this->response = new Response($throwable->getMessage() . PHP_EOL . $throwable->getTraceAsString(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return Throwable
     */
    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }

    /**
     * @param Throwable $throwable
     */
    public function setThrowable(Throwable $throwable): void
    {
        $this->throwable = $throwable;
    }


}