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
     * @var string
     */
    protected $lang;

    /**
     * @var bool
     */
    protected $isAnnotationRoute = false;

    /**
     * AppRoute constructor.
     * @param string $path
     * @param string $controllerClass
     * @param string $methodName
     */
    public function __construct(string $path, string $controllerClass, string $methodName, $resolveRoute = true)
    {
        $this->path = mb_strtolower($path);
        $this->controllerClass = $controllerClass;
        $this->methodName = $methodName;
        $this->resolveArea();
        if ($resolveRoute)
            $this->resolveRouteObject();
    }

    public static function fromRoute(Route $route)
    {
        $controller = $route->getDefault('controller');
        $appRoute = new self($route->getPath(), $controller[0], $controller[1], false);
        $appRoute->setRoute($route);
        return $appRoute;
    }

    public static function fromRouteParams($path, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        return self::fromRoute(new Route(
            $path,
            $defaults,
            $requirements,
            $options,
            $host,
            $schemes,
            $methods,
            $condition
        ));
    }

    public function resolveArea()
    {
        $this->area = mb_strtolower(explode('\\', $this->controllerClass, 4)[2]);
    }

    public function resolveRouteObject(array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        $this->route = new Route(
            $this->path,
            ['controller' => [$this->controllerClass, $this->methodName],],
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

    /**
     * @param Route $route
     * @return AppRoute
     */
    public function setRoute(Route $route): AppRoute
    {
        $this->route = mb_strtolower($route);
        return $this;
    }

    /**
     * @return string
     */
    public function getLang(): ?string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     * @return AppRoute
     */
    public function setLang(string $lang): AppRoute
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAnnotationRoute(): bool
    {
        return $this->isAnnotationRoute;
    }

    /**
     * @param bool $isAnnotationRoute
     * @return AppRoute
     */
    public function setIsAnnotationRoute(bool $isAnnotationRoute): AppRoute
    {
        $this->isAnnotationRoute = $isAnnotationRoute;
        return $this;
    }


}