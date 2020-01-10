<?php


namespace ErkinApp\Route;


use ArrayIterator;
use Countable;
use IteratorAggregate;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


class AppRouteCollection implements IteratorAggregate, Countable
{
    /**
     * @var AppRoute[]
     */
    protected $appRoutes = [];


    public function getIterator()
    {
        return new ArrayIterator($this->appRoutes);
    }


    public function count()
    {
        return count($this->appRoutes);
    }

    public function add(AppRoute $route)
    {
        $this->appRoutes[$route->getPath()] = $route;
    }

    public function all()
    {
        return $this->appRoutes;
    }

    public function get($path)
    {
        return isset($this->appRoutes[$path]) ? $this->appRoutes[$path] : null;
    }

    public function resolve($path)
    {
        foreach ($this->all() as $appRoute) {
            $route = $appRoute->getRoute();

            if (preg_match($route->compile()->getRegex(), $path))
                return $appRoute;
        }
        return false;
    }

    public function remove($path)
    {
        foreach ((array)$path as $n) {
            unset($this->appRoutes[$n]);
        }
    }

    public function convertToRouteCollection()
    {
        $routeCollection = new RouteCollection();
        foreach ($this->all() as $appRoute) {
            $routeCollection->add($appRoute->getPath(), $appRoute->getRoute());
        }
        return $routeCollection;
    }

    public function registerRoutes()
    {
        foreach ($this->all() as $appRoute) {
            ErkinApp()->Routes()->add($appRoute->getRoute()->getPath(), $appRoute->getRoute());
        }
    }

    public function registerRouteOnPath($path)
    {
        ErkinApp()->Routes()->add($path, $this->get($path)->getRoute());
    }

    public function registerRouteViaRoute(Route $route)
    {
        ErkinApp()->Routes()->add($route->getPath(), $route);
    }

    public function registerRouteViaAppRoute(AppRoute $appRoute)
    {
        $this->registerRouteViaRoute($appRoute->getRoute());
    }
}