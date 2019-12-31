<?php


namespace {

    use ErkinApp\ErkinApp;

    /**
     * @return ErkinApp
     */
    function ErkinApp()
    {
        return ErkinApp::getInstance();
    }
}

namespace ErkinApp\Helpers {

    use ErkinApp\Constants;
    use ErkinApp\Events\ControllerNotFoundEvent;
    use ErkinApp\Events\ErrorEvent;
    use ErkinApp\Events\Events;
    use ErkinApp\Events\RoutingEvent;
    use ErkinApp\Exceptions\ErkinAppException;
    use Symfony\Component\HttpFoundation\Response;
    use Whoops\Handler\CallbackHandler;
    use Whoops\Handler\Handler;
    use Whoops\Run;

    /**
     * @param null $basePath
     * @throws ErkinAppException
     */
    function handleApp()
    {
        if (!defined('BASE_PATH')) {
            throw new ErkinAppException("BASE_PATH is not defined !");
        }
        if (!defined('APP_PATH')) {
            define('APP_PATH', BASE_PATH . '/app');
        }
        if (!defined('LANGUAGE_PATH')) {
            define('LANGUAGE_PATH', BASE_PATH . '/languages');
        }

        $whoops = new Run;
        $whoops->prependHandler(new CallbackHandler(function ($exception, $inspector, $run) {
            /** @var ErrorEvent $errorEvent */
            $errorEvent = ErkinApp()->Dispatcher()->dispatch(new ErrorEvent(ErkinApp()->Request(), $exception), Events::ERROR);
            if ($errorEvent->hasResponse()) {
                $errorEvent->getResponse()->send();
            }
            return Handler::DONE;
        }));
        $whoops->register();


        // Our framework is being handling itself
        $app = ErkinApp();
        $request = $app->Request();
        require_once APP_PATH . '/events.php';


        $app->Dispatcher()->dispatch(new RoutingEvent($request), Events::ROUTING);


        $paths = explode('/', $request->getPathInfo());

        if (count($paths) < 2 && !$app->Routes()->get($request->getPathInfo())) {
            (new Response())
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setContent("Route error")->send();
            die;
        }

        $area = Constants::AREA_FRONTEND;
        $defaultController = ROUTE_FRONTEND_DEFAULT_CONTROLLER;
        $defaultMethod = ROUTE_FRONTEND_DEFAULT_METHOD;

        switch (strtolower($paths[1])) {
            case strtolower(BACKEND_AREA_NAME):
                $area = Constants::AREA_BACKEND;
                $defaultController = ROUTE_BACKEND_DEFAULT_CONTROLLER;
                $defaultMethod = ROUTE_BACKEND_DEFAULT_METHOD;
                break;
            case 'api':
                $area = Constants::AREA_API;
                $defaultController = ROUTE_API_DEFAULT_CONTROLLER;
                $defaultMethod = ROUTE_API_DEFAULT_METHOD;
                break;
        }
        $app->setCurrentArea($area);

        if (in_array(strtolower($paths[1]), ['frontend', strtolower(BACKEND_AREA_NAME), strtolower(API_AREA_NAME)])) {
            $controller = !empty($paths[2]) ? ucfirst(strtolower($paths[2])) : $defaultController;
            $method = isset($paths[3]) && !empty($paths[3]) ? $paths[3] : $defaultMethod;
        } else {
            $controller = !empty($paths[1]) ? ucfirst(strtolower($paths[1])) : $defaultController;
            $method = isset($paths[2]) && !empty($paths[2]) ? $paths[2] : $defaultMethod;
        }


        $classname = 'Application\\Controller\\' . $area . '\\' . $controller;


//    $compiled_routes = [];
        $matched = false;
        foreach ($app->Routes()->all() as $name => $route) {
            if ($matched = preg_match($route->compile()->getRegex(), $request->getPathInfo())) break;
        }

//    $app->map($request->getPathInfo(),
//        [
//            $classname,
//            $method
//        ]);

        if (!$matched) {
            if (!class_exists($classname)) {

                $controllerNotFoundEvent = $app->Dispatcher()->dispatch(new ControllerNotFoundEvent($request), Events::CONTROLLER_NOT_FOUND);
                if ($controllerNotFoundEvent->hasResponse()) {
                    $controllerNotFoundEvent->getResponse()->send();
                } else {
                    (new Response())
                        ->setStatusCode(Response::HTTP_NOT_FOUND)
                        ->setContent("Class <strong> {$classname} </strong> not found for route (" . $request->getPathInfo() . ")!")
                        ->send();
                }
                die;
            }
            $app->map($request->getPathInfo(),
                [
                    $classname,
                    $method
                ]);
        }


        $app->setCurrentController($classname);
        $app->setCurrentMethod($method);

        $response = $app->handle($request);
        $response->send();


    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    function getUserFrontend($key = '')
    {
        return ErkinApp()->UserFrontend($key);
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    function getUserBackend($key = '')
    {
        return ErkinApp()->UserBackend($key);
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    function getUserApi($key = '')
    {
        return ErkinApp()->UserApi($key);
    }

    /**
     * @param $__filename
     * @param array $__data
     * @return bool|false|string
     */
    function getView($__filename, $__data = [], $includeParts = false)
    {
        $__filename = ltrim($__filename, '/');

        $viewfile = APP_PATH . "/View/";
        if (!s($__filename)->startsWithAny(['Frontend', 'Backend', 'Api'])) {
            $viewfile .= ErkinApp()->getCurrentArea();
        }
        $viewfile .= "/$__filename.php";

        if (!file_exists($viewfile)) return false;


        if (is_array($__data))
            extract($__data);

        ob_start();

        if ($includeParts && file_exists(APP_PATH . '/View/' . ErkinApp()->getCurrentArea() . '/_parts/head.php'))
            include APP_PATH . '/View/' . ErkinApp()->getCurrentArea() . '/_parts/head.php';

        include $viewfile;

        if ($includeParts && file_exists(APP_PATH . '/View/' . ErkinApp()->getCurrentArea() . '/_parts/end.php'))
            include APP_PATH . '/View/' . ErkinApp()->getCurrentArea() . '/_parts/end.php';

        return ob_get_clean();
    }

    /**
     * @param $path
     * @return bool
     */
    function loadLibrary($path)
    {
        $filename = BASE_PATH . '/libraries/' . ltrim($path, '/');
        if (!file_exists($filename)) return false;

        include_once $filename;
        return true;
    }
}