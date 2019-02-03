<?php
/**
 * Created by PhpStorm.
 * User: erkin
 * Date: 1.01.2019
 * Time: 16:49
 */

namespace ErkinApp\Events;


use ErkinApp\Controller;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * ControllerEvent constructor.
     * @param Controller $controller
     * @param string $method
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