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

    use ErkinApp\Event\ErrorEvent;
    use ErkinApp\Event\Events;
    use ErkinApp\Event\NotFoundEvent;
    use ErkinApp\Event\RoutingEvent;
    use ErkinApp\Exception\ErkinAppException;
    use Exception;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Whoops\Handler\CallbackHandler;
    use Whoops\Handler\Handler;
    use Whoops\Run;

    /**
     * @throws ErkinAppException
     * @throws Exception
     */
    function handleApp()
    {
        if (!defined('BASE_PATH')) {
            throw new ErkinAppException("BASE_PATH is not defined !");
        }
        if (!defined('APP_PATH')) {
            define('APP_PATH', realpath(BASE_PATH . '/app'));
        }
        if (!defined('LANGUAGE_PATH')) {
            define('LANGUAGE_PATH', realpath(BASE_PATH . '/languages'));
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
        $app->Localization()->determineLanguage();

        $app->Dispatcher()->dispatch(new RoutingEvent($request), Events::ROUTING);

        $appRoute = $app->AppRoutes()->resolve(strtolower($request->getPathInfo()));

        if ($appRoute) {
            if ($appRoute->isAnnotationRoute()) {
                $langAppRoute = $app->AppRoutes()->findLanguageRoute($appRoute);
                if ($langAppRoute) {
                    (new RedirectResponse($langAppRoute->getPath()))->send();
                }
            }

            $app->AppRoutes()->registerRouteViaAppRoute($appRoute);
            $app->setCurrentAppRoute($appRoute);
        } else {

            if (strtolower($request->getPathInfo()) !== rtrim(strtolower($request->getPathInfo()), '/')) {
                (new RedirectResponse(rtrim(strtolower($request->getRequestUri()), '/'), 308))->send();
            }

            /** @var NotFoundEvent $notFoundEvent */
            $notFoundEvent = $app->Dispatcher()->dispatch(new NotFoundEvent('Route not found "' . $request->getPathInfo() . '"', $request), Events::NOT_FOUND);
            if ($notFoundEvent->hasResponse()) {
                $notFoundEvent->getResponse()->send();
            } else {
                (new Response())
                    ->setStatusCode(Response::HTTP_NOT_FOUND)
                    ->setContent('Route not found "' . $request->getPathInfo() . '"')
                    ->send();
            }
            die;
        }

        $response = $app->handle($request);
        $response->send();
    }

    /**
     * @param string $key
     * @return bool|mixed
     * @throws Exception
     */
    function getUserFrontend($key = '')
    {
        return ErkinApp()->UserFrontend($key);
    }

    /**
     * @param string $key
     * @return bool|mixed
     * @throws Exception
     */
    function getUserBackend($key = '')
    {
        return ErkinApp()->UserBackend($key);
    }

    /**
     * @param string $key
     * @return bool|mixed
     * @throws Exception
     */
    function getUserApi($key = '')
    {
        return ErkinApp()->UserApi($key);
    }

    /**
     * @param $filename
     * @param array $data
     * @param bool $includeParts
     * @return bool|false|string
     * @throws Exception
     */
    function getView($filename, $data = [], $includeParts = false)
    {
        return ErkinApp()->TemplateManager()->getCompiled($filename, $data, $includeParts);
    }

    /**
     * @param $path
     * @return bool
     */
    function loadLibrary($path)
    {
        $filename = BASE_PATH . '/libraries/' . ltrim($path, '/') . ".php";
        if (!file_exists($filename)) return false;

        include_once $filename;
        return true;
    }

    /**
     * @throws ErkinAppException
     */
    function loadConfig()
    {
        $configFilePath = SYS_PATH . '/config/config.php';
        if (!file_exists($configFilePath))
            throw new ErkinAppException("Config file not found");

        $config = include $configFilePath;
    }

    function isCommandLineInterface()
    {
        if (defined('STDIN')) {
            return true;
        }

        if (php_sapi_name() === 'cli') {
            return true;
        }

        if (array_key_exists('SHELL', $_ENV)) {
            return true;
        }

        if (empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
            return true;
        }

        if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
            return true;
        }

        return false;
    }
}