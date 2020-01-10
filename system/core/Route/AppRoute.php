<?php

namespace ErkinApp\Route;

use Symfony\Component\Routing\Route;

class AppRoute
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $controllerClass;

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var string
     */
    protected $area;

    /**
     * @var Route
     */
    protected $route;

    /**
     * AppRoute constructor.
     * @param string $path
     * @param string $controllerClass
     * @param string $methodName
     */
    public function __construct(string $path, string $controllerClass, string $methodName)
    {
        $this->path = $path;
        $this->controllerClass = $controllerClass;
        $this->methodName = $methodName;
        $this->resolveArea();
        $this->resolveRouteObject();
    }

    public function resolveArea()
    {
        $this->area = mb_strtolower(explode('\\', $this->controllerClass, 4)[2]);
    }

    public function resolveRouteObject(array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        $this->route = new Route(
            $this->path,
            [
                'controller' => [$this->controllerClass, $this->methodName],
            ],
            $requirements,
            $options,
            $host,
            $schemes,
            $methods,
            $condition
        );
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return string
     */
    public function getArea(): string
    {
        return $this->area;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }


}