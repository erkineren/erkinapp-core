<?php

namespace ErkinApp\Helpers {

    use ErkinApp\ErkinApp;

    /**
     * @param string $path
     * @return string
     */
    function siteUrl($path = '')
    {
        $request = ErkinApp::getInstance()->Request();
        $url = $request->getSchemeAndHttpHost() . $request->getBasePath() . '/' . $path;

        return $url;
    }

    /**
     * @param string $path
     * @return string
     */
    function backendUrl($path = '')
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
    function assetUrl($path, $version = true)
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
    function selfMethodUrl($path_as_args = '')
    {
        return siteUrl(rtrim(ErkinApp::getInstance()->getCurrentActionMethodPath(), "/") . '/' . $path_as_args);
    }

    /**
     * @param string $path
     * @param bool $rtrimslash
     * @return string
     */
    function uriString($path = '', $rtrimslash = true)
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
    function isCurrentPath($paths)
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
    function isCurrentActionPath($actionMethodPath)
    {
        return ErkinApp::getInstance()->getCurrentActionMethodPath() == $actionMethodPath;
    }

    /**
     * @param $controllerPath
     * @return bool
     */
    function isCurrentControllerPath($controllerPath)
    {
        return ErkinApp::getInstance()->getCurrentControllerPath() == strtolower($controllerPath);
    }
}
