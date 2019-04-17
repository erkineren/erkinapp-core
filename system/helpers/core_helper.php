<?php

use ErkinApp\ErkinApp;
use ErkinApp\Events\ControllerNotFoundEvent;
use ErkinApp\Events\Events;
use ErkinApp\Events\RoutingEvent;
use ErkinApp\Exceptions\ErkinAppException;
use Symfony\Component\HttpFoundation\Response;
use Whoops\Handler\PlainTextHandler;
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
    $whoops->pushHandler(new PlainTextHandler());
    $whoops->register();


    // Our framework is being handling itself
    $app = ErkinApp();
    $request = $app->Request();
    require_once APP_PATH . '/events.php';


    $app->Dispatcher()->dispatch(Events::ROUTING, new RoutingEvent($request));


    $paths = explode('/', $request->getPathInfo());

    if (count($paths) < 2 && !$app->Routes()->get($request->getPathInfo())) {
        (new Response())
            ->setStatusCode(Response::HTTP_BAD_REQUEST)
            ->setContent("Route error")->send();
        die;
    }

    $area = 'Frontend';
    $defaultController = ROUTE_FRONTEND_DEFAULT_CONTROLLER;
    $defaultMethod = ROUTE_FRONTEND_DEFAULT_METHOD;

    switch (strtolower($paths[1])) {
        case strtolower(BACKEND_AREA_NAME):
            $area = 'Backend';
            $defaultController = ROUTE_BACKEND_DEFAULT_CONTROLLER;
            $defaultMethod = ROUTE_BACKEND_DEFAULT_METHOD;
            break;
        case 'api':
            $area = 'Api';
            $defaultController = ROUTE_API_DEFAULT_CONTROLLER;
            $defaultMethod = ROUTE_API_DEFAULT_METHOD;
            break;
    }

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

            $controllerNotFoundEvent = $app->Dispatcher()->dispatch(Events::CONTROLLER_NOT_FOUND, new ControllerNotFoundEvent($request));
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


    $app->setCurrentArea($area);
    $app->setCurrentContoller($classname);
    $app->setCurrentMethod($method);

    $response = $app->handle($request);
    $response->send();


}

/**
 * @return ErkinApp
 */
function ErkinApp()
{
    return ErkinApp::getInstance();
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

if (!function_exists('getView')) {
    function getView($__filename, $__data = [])
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
        include $viewfile;
        return ob_get_clean();
    }
}

if (!function_exists('loadLibrary')) {
    function loadLibrary($path)
    {
        $filename = BASE_PATH . '/libraries/' . ltrim($path, '/');
        if (!file_exists($filename)) return false;

        include_once $filename;
    }
}