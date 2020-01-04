<?php

namespace ErkinApp\Events;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class ViewFileNotFoundEvent extends Event
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
     * @var string
     */
    private $filename;

    /**
     * RequestEvent constructor.
     * @param Request $request
     * @param string $filename
     */
    public function __construct(Request $request, string $filename)
    {
        $this->request = $request;
        $this->filename = $filename;
        $this->setResponse(new Response("View file not found : <strong>{$filename}</strong>"));
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
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

}