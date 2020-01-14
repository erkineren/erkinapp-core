<?php

namespace ErkinApp\Event;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotFoundEvent extends BaseEvent
{
    protected $message;

    /**
     * NotFoundEvent constructor.
     * @param $message
     */
    public function __construct($message, Request $request = null, Response $response = null)
    {
        parent::__construct($request, $response);
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return NotFoundEvent
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }


}