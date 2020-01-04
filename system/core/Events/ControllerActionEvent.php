<?php


namespace ErkinApp\Events;


use ErkinApp\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class ControllerActionEvent extends Event
{
    /** @var Controller */
    public $controller;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $method_parameters;
    /**
     * @var Request
     */
    public $request;
    /**
     * @var Response
     */
    public $response;

    /**
     * ControllerActionEvent constructor.
     * @param Controller $controller
     * @param $method
     * @param $method_parameters
     * @param Request $request
     * @param Response|null $response
     */
    public function __construct(Controller $controller, $method, $method_parameters, Request &$request, Response &$response = null)
    {
        $this->controller = $controller;
        $this->method = $method;
        $this->method_parameters = $method_parameters;
        $this->request = $request;
        $this->response = $response;
    }


}