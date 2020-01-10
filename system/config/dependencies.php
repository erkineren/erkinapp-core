<?php

use Doctrine\Common\Annotations\AnnotationReader;
use ErkinApp\Component\Config;
use ErkinApp\Component\Localization;
use ErkinApp\Container;
use ErkinApp\Route\AppRoute;
use ErkinApp\Route\AppRouteCollection;
use ErkinApp\Template\TemplateManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

return function () {
    $container = ErkinApp()->Container();

    $container[Config::class] = function (Container $c) {
        return (new Config())->load();
    };

    $container[Localization::class] = function (Container $c) {
        return new Localization();
    };

    $container[EventDispatcher::class] = function (Container $c) {
        return new EventDispatcher();
    };

    $container[Request::class] = function (Container $c) {
        $request = Request::createFromGlobals();
        $request->setSession(new Session());
        return $request;
    };

    $container[Logger::class] = function (Container $c) {
        $log = new Logger('app');
        $log->pushHandler(new StreamHandler(LOGS_PATH . '/app.log', Logger::WARNING));
        return $log;
    };

    $container[TemplateManager::class] = function (Container $c) {
        return (new TemplateManager())->loadTemplate();
    };

    $container['classMaps'] = function (Container $c) {
        $fnc = function ($ctrlFile) {
            $classname = '\\Application' . str_replace(APP_PATH, '', $ctrlFile);
            $classname = substr($classname, 0, strrpos($classname, "."));
            $classname = str_replace('/', '\\', $classname);
            return $classname;
        };

        $ctrlIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath(APP_PATH . '/Controller'), RecursiveDirectoryIterator::SKIP_DOTS));
        $modelIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath(APP_PATH . '/Model'), RecursiveDirectoryIterator::SKIP_DOTS));

        $controllers = [];
        /** @var SplFileInfo $ctrlFile */
        foreach ($ctrlIterator as $ctrlFile) {
            if (pathinfo($ctrlFile, PATHINFO_EXTENSION) == "php") {
                $classname = $fnc($ctrlFile->getRealPath());
                $controllers[$classname] = realpath($ctrlFile);
            }
        }

        $models = [];
        /** @var SplFileInfo $ctrlFile */
        foreach ($modelIterator as $ctrlFile) {
            if (pathinfo($ctrlFile, PATHINFO_EXTENSION) == "php") {
                $classname = $fnc($ctrlFile->getRealPath());
                $models[$classname] = realpath($ctrlFile);
            }
        }

        return [
            'controllers' => $controllers,
            'models' => $models,
        ];
    };

    $container[AppRouteCollection::class] = function (Container $c) {
        $controllers = $c->getClassMaps()['controllers'];
        $annotationReader = new AnnotationReader();

        $routes = [];
        $routesLater = [];
        foreach ($controllers as $controllerClass => $controllerPath) {
            $ref = new ReflectionClass($controllerClass);
            if ($ref->isAbstract() || $ref->isInterface() || $ref->isTrait()) continue;

            $parts = explode('\\', $ref->getName(), 4);

            $area = mb_strtolower($parts[2]);
            $isFrontEnd = $area == 'frontend';
            $ctrlRoutePath = mb_strtolower($parts[3]);
            $ctrlRoutePath = str_replace('\\', '/', $ctrlRoutePath);

            $defaultController = ROUTE_FRONTEND_DEFAULT_CONTROLLER;
            $defaultMethod = ROUTE_FRONTEND_DEFAULT_METHOD;

            switch ($area) {
                case strtolower(BACKEND_AREA_NAME):
                    $defaultController = ROUTE_BACKEND_DEFAULT_CONTROLLER;
                    $defaultMethod = ROUTE_BACKEND_DEFAULT_METHOD;
                    break;
                case strtolower(API_AREA_NAME):
                    $defaultController = ROUTE_API_DEFAULT_CONTROLLER;
                    $defaultMethod = ROUTE_API_DEFAULT_METHOD;
                    break;
            }

            $addRoute = function ($path, ReflectionMethod $method, $later = false) use (&$routes, &$routesLater, $controllerClass) {

                if ($later) $array = &$routesLater;
                else $array = &$routes;

                $addDirectly = true;
                foreach ($method->getParameters() as $parameter) {
                    $argString = '{' . $parameter->getName() . '}';
                    if (strpos($path, $argString) !== false) continue;
                    $addDirectly = false;

                    $path .= "/$argString";
                    $appRoute = new AppRoute($path, $controllerClass, $method->getName());
                    if ($parameter->isOptional()) {
                        $appRoute->getRoute()->setDefault($parameter->getName(), $parameter->getDefaultValue());
                    }

                    $array[$path][$controllerClass . '@' . $method->getName()] = $appRoute;
                }

                if ($addDirectly) {
                    $appRoute = new AppRoute($path, $controllerClass, $method->getName());
                    $array[$path][$controllerClass . '@' . $method->getName()] = $appRoute;
                }


                return $appRoute;
            };

            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->isPublic() && !$method->isConstructor() && !$method->isStatic() && $method->class == $ref->getName()) {
                    $methodName = strtolower($method->getName());

                    $addRoute("/$area/$ctrlRoutePath/$methodName", $method);

                    if ($methodName == $defaultMethod)
                        $addRoute("/$area/$ctrlRoutePath", $method);

                    if ($ref->getShortName() == $defaultController) {
                        $addRoute("/$area/$methodName", $method, true);
                        if ($methodName == $defaultMethod)
                            $addRoute("/$area", $method);
                    }

                    if ($isFrontEnd) {
                        $addRoute("/$ctrlRoutePath/$methodName", $method);
                        if ($methodName == $defaultMethod)
                            $addRoute("/$ctrlRoutePath", $method);
                        if ($ref->getShortName() == $defaultController) {
                            $addRoute("/$methodName", $method, true);
                            if ($methodName == $defaultMethod)
                                $addRoute("/", $method);
                        }
                    }

                    /*
                     * Add Route annotatations
                     */
                    $methodAnnotations = $annotationReader->getMethodAnnotations($method);
                    foreach ($methodAnnotations as $an) {
                        if ($an instanceof Symfony\Component\Routing\Annotation\Route) {
                            $appRoute = $addRoute($an->getPath(), $method);
                            $appRoute->resolveRouteObject(
                                $an->getRequirements(),
                                $an->getOptions(),
                                $an->getHost(),
                                $an->getSchemes(),
                                $an->getMethods(),
                                $an->getCondition()
                            );
                        }
                    }
                }
            }
        }


        $routes = array_merge($routes, $routesLater);
        $routes = array_map('reset', $routes);
//\ErkinApp\Helpers\debugPrint($routes);
        $appRouteCollection = new AppRouteCollection();
        foreach ($routes as $route) {
            $appRouteCollection->add($route);
        }
        return $appRouteCollection;
    };

};