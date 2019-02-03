<?php

use ErkinApp\ErkinApp;
use Symfony\Component\HttpFoundation\Response;

/**
 * @param null $basePath
 * @throws \ErkinApp\Exceptions\ErkinAppException
 */
function handleApp()
{
    if (!defined('BASE_PATH')) {
        throw new \ErkinApp\Exceptions\ErkinAppException("BASE_PATH is not defined !");
    }

    define('LANGUAGE_PATH', BASE_PATH . '/languages');

    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
    $whoops->register();


// Our framework is now handling itself the request
    $app = ErkinApp();

    $request = $app->Request();


    $paths = explode('/', $request->getPathInfo());

    if (count($paths) >= 2) {

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

//    var_dump(method_exists('Application\\Controller\\' . $area . '\\Index', $method));die;

        if (!class_exists($classname)) {

            (new Response())
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setContent("Class <strong> {$classname} </strong> not found !")->send();
            die;
        }

        $app->map($request->getPathInfo(),
            [
                $classname,
                $method
            ]);
    }

    require_once BASE_PATH . '/app/events.php';

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


