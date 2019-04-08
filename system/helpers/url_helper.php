<?php

use ErkinApp\ErkinApp;

function site_url($path = '')
{
    $request = ErkinApp::getInstance()->Request();
    $url = $request->getScheme() . '://' . $request->getHost() . $request->getBasePath() . '/' . $path;

    return $url;
}

function backend_url($path = '')
{
    $request = ErkinApp::getInstance()->Request();
    $url = $request->getScheme() . '://' . $request->getHost() . $request->getBasePath() . '/' . BACKEND_AREA_NAME . '/' . $path;

    return $url;
}

function asset_url($path, $version = true)
{
    $request = ErkinApp::getInstance()->Request();

    $url = $request->getScheme() . '://' . $request->getHost() . $request->getBasePath() . '/assets/' . $path;

    if ($version && file_exists('assets/' . $path))
        $url .= '?v=' . filemtime('assets/' . $path);

    return $url;
}

function self_method_url($path_as_args = '')
{
    return site_url(rtrim(ErkinApp::getInstance()->getCurrentActionMethodPath(), "/") . '/' . $path_as_args);
}

function uri_string($path = '', $rtrimslash = true)
{
    $request = ErkinApp::getInstance()->Request();

    $url = $request->getScheme() . '://' . $request->getHost() . $request->getBasePath() . $request->getPathInfo();

    if ($rtrimslash)
        $url = rtrim($url, "/") . '/';

    return $url . $path;
}

function is_current_path($paths)
{
    if (!is_array($paths)) $paths = [$paths];

    foreach ($paths as $path) {
        if (ErkinApp::getInstance()->Request()->getPathInfo() == '/' . rtrim($path, '/')) return true;
    }

    return false;
}

function is_current_action_path($actionMethodPath)
{
    return ErkinApp::getInstance()->getCurrentActionMethodPath() == $actionMethodPath;
}

function is_current_controller_path($controllerPath)
{
    return ErkinApp::getInstance()->getCurrentContollerPath() == strtolower($controllerPath);
}

