<?php

namespace ErkinApp\Event;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ViewFileNotFoundEvent extends BaseEvent
{
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
        parent::__construct($request);
        $this->request = $request;
        $this->filename = $filename;
        $this->setResponse(new Response("View file not found : <strong>{$filename}</strong>"));
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