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
            ErkinApp()->RouteCollection()->add($appRoute->getRoute()->getPath(), $appRoute->getRoute());
        }
    }

    public function registerRouteOnPath($path)
    {
        ErkinApp()->RouteCollection()->add($path, $this->get($path)->getRoute());
    }

    public function registerRouteViaRoute(Route $route)
    {
        ErkinApp()->RouteCollection()->add($route->getPath(), $route);
    }

    public function registerRouteViaAppRoute(AppRoute $appRoute)
    {
        $this->registerRouteViaRoute($appRoute->getRoute());
    }

    public function findLanguageRoute(AppRoute $appRoute): ?AppRoute
    {
        $lang = ErkinApp()->Localization()->getCurrentLanguage();

        if ($lang != $appRoute->getLang()) {
            foreach ($this->all() as $appRouteItem) {
                if ($appRouteItem->getControllerClass() == $appRoute->getControllerClass() && $appRouteItem->getMethodName() == $appRoute->getMethodName() && $appRouteItem->getLang() == $lang) {
                    return $appRouteItem;
                }
            }
        }

        return null;
    }

    public function findRouteFromPath(string $path): ?AppRoute
    {
        $path = mb_strtolower($path);
        foreach ($this->all() as $appRouteItem) {
            if ($appRouteItem->getPath() == $path) {
                return $appRouteItem;
            }
        }
        return null;
    }

    public function findLanguagePath(string $path): ?string
    {
        $path = mb_strtolower($path);
        $lang = ErkinApp()->Localization()->getCurrentLanguage();
        $appRoute = $this->findRouteFromPath($path);
        if ($appRoute) {
            $appLangRoute = $this->findLanguageRoute($appRoute);
            if ($appLangRoute)
                return $appLangRoute->getPath();
        }
        return null;
    }

}