<?php

namespace ErkinApp\Helpers {

    use ErkinApp\ErkinApp;

    /**
     * @param string $path
     * @return string
     */
    function site_url($path = '')
    {
        $request = ErkinApp::getInstance()->Request();
        $url = $request->getSchemeAndHttpHost() . $request->getBasePath() . '/' . $path;

        return $url;
    }

    /**
     * @param string $path
     * @return string
     */
    function backend_url($path = '')
    {
        $request = ErkinApp::getInstance()->Request();
        $url = $request->getSchemeAndHttpHost() . $request->getBasePath() . '/' . BACKEND_AREA_NAME . '/' . $path;

        return $url;
    }

    /**
     * @param $path
     * @param bool $version
     * @return string
     */
    function asset_url($path, $version = true)
    {
        $request = ErkinApp::getInstance()->Request();

        $url = $request->getSchemeAndHttpHost() . $request->getBasePath() . '/assets/' . $path;

        if ($version && file_exists('assets/' . $path))
            $url .= '?v=' . filemtime('assets/' . $path);

        return $url;
    }

    /**
     * @param string $path_as_args
     * @return string
     */
    function self_method_url($path_as_args = '')
    {
        return site_url(rtrim(ErkinApp::getInstance()->getCurrentActionMethodPath(), "/") . '/' . $path_as_args);
    }

    /**
     * @param string $path
     * @param bool $rtrimslash
     * @return string
     */
    function uri_string($path = '', $rtrimslash = true)
    {
        $request = ErkinApp::getInstance()->Request();

        $url = $request->getSchemeAndHttpHost() . $request->getBasePath() . $request->getPathInfo();

        if ($rtrimslash)
            $url = rtrim($url, "/") . '/';

        return $url . $path;
    }

    /**
     * @param $paths
     * @return bool
     */
    function is_current_path($paths)
    {
        if (!is_array($paths)) $paths = [$paths];

        foreach ($paths as $path) {
            if (ErkinApp::getInstance()->Request()->getPathInfo() == '/' . rtrim($path, '/')) return true;
        }

        return false;
    }

    /**
     * @param $actionMethodPath
     * @return bool
     */
    function is_current_action_path($actionMethodPath)
    {
        return ErkinApp::getInstance()->getCurrentActionMethodPath() == $actionMethodPath;
    }

    /**
     * @param $controllerPath
     * @return bool
     */
    function is_current_controller_path($controllerPath)
    {
        return ErkinApp::getInstance()->getCurrentContollerPath() == strtolower($controllerPath);
    }
}
