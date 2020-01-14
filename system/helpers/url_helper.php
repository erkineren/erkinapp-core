<?php

namespace ErkinApp\Helpers {

    use ErkinApp\ErkinApp;
    use Exception;

    function normalizeUrlPath($path)
    {
        return str_replace('\\', '/', $path);
    }

    function convertPublicPathToUrl($realpath, $revision = false)
    {
        if (strpos($realpath, PUBLIC_PATH) === false)
            return '';

        return siteUrl(str_replace(PUBLIC_PATH, '', $realpath));
    }

    function convertPublicPathToUrlPath($realpath, $revision = false)
    {
        if (strpos($realpath, PUBLIC_PATH) === false)
            return '';

        return str_replace(PUBLIC_PATH, '', $realpath);
    }

    /**
     * @param string $path
     * @return string
     * @throws Exception
     */
    function siteUrl($path = '')
    {
        $request = ErkinApp::getInstance()->Request();
        return $request->getSchemeAndHttpHost() . $request->getBasePath() . '/' . ltrim(normalizeUrlPath($path), '/');
    }

    /**
     * @param string $path
     * @return string
     * @throws Exception
     */
    function backendUrl($path = '')
    {
        $request = ErkinApp::getInstance()->Request();
        return $request->getSchemeAndHttpHost() . $request->getBasePath() . '/' . BACKEND_AREA_NAME . '/' . ltrim(normalizeUrlPath($path), '/');
    }

    /**
     * @param $path
     * @param bool $version
     * @return string
     * @throws Exception
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
     * @throws Exception
     */
    function selfMethodUrl($path_as_args = '')
    {
        return siteUrl(rtrim(ErkinApp::getInstance()->getCurrentActionMethodPath(), "/") . '/' . $path_as_args);
    }

    /**
     * @param string $path
     * @param bool $rtrimslash
     * @return string
     * @throws Exception
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
     * @throws Exception
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
