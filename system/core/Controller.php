<?php

namespace ErkinApp;


use Envms\FluentPDO\Query;
use ErkinApp\Events\Events;
use ErkinApp\Events\ViewFileNotFoundEvent;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use function ErkinApp\Helpers\getClassShortName;

/**
 * Class Controller
 * @package ErkinApp
 *
 * @property Request $request
 * @property EventDispatcher $dispatcher
 * @property SessionInterface $sessions
 * @property ParameterBag $cookies
 * @property Model[] $models
 * @property string $area
 * @property Query $db
 * @property Container $container
 * @property Logger $logger
 */
abstract class Controller
{

    /**
     * BaseController constructor.
     * @throws Exceptions\ErkinAppException
     */
    public function __construct()
    {
        ErkinApp::getInstance()->setCurrentArea(explode('\\', get_called_class())[2]);
    }

    /**
     * @param $name
     * @return bool|\ErkinApp\Container|Model|Model[]|mixed|EventDispatcher|ParameterBag|Request|SessionInterface|null
     * @throws \Exception
     */
    public function __get($name)
    {
        return ErkinApp()->Get($name);
    }

    /**
     * @param string $class
     * @return bool|Model
     * @throws Exceptions\ErkinAppException
     */
    public function getModel($class = '')
    {
        if (!$class) return $this->model;
        return ErkinApp()->Models($class);
    }

    /**
     * @param string $dbkey
     * @return \PDO|null
     * @throws \Exception
     */
    public function getDb($dbkey = 'default')
    {
        return ErkinApp()->DB($dbkey);
    }

    public function renderViewPlain($__view = '', $__data = [])
    {
        return $this->renderView($__view, $__data, false);
    }

    public function renderView($__view = '', $__data = [], $includeParts = true)
    {
        if (is_array($__view)) {
            $__data = $__view;
            $__view = '';
        }
        /*
         * Eğer view parametresi boş gönderildi ise varsayılan view dosyasını bul
         *
         * Kurallar:
         *  1. Controller class short name
         *  2. Controller method name
         *
         * Örnek: anasayfa için index/index.php dosyasını bul
         */
        if (!$__view) {
            $__called_controller_short_name = strtolower(getClassShortName(get_class(debug_backtrace()[1]['object'])));
            $__view = $__called_controller_short_name . '/' . debug_backtrace()[1]['function'];
            if (strpos($__view, 'renderViewPlain') !== false)
                $__view = $__called_controller_short_name . '/' . debug_backtrace()[2]['function'];
        }

        $__filename = sprintf(APP_PATH . '/View/' . $this->area . '/%s.php', $__view);

        if (!file_exists($__filename)) {
            /** @var ViewFileNotFoundEvent $viewFileNotFoundEvent */
            $viewFileNotFoundEvent = $this->dispatch(new ViewFileNotFoundEvent($this->request, $__filename), Events::VIEW_FILE_NOT_FOUND);
            if ($viewFileNotFoundEvent->hasResponse()) return $viewFileNotFoundEvent->getResponse();
            die;
        }

        if (is_array($__data))
            extract($__data);
        ob_start();

        if ($includeParts && file_exists(APP_PATH . '/View/' . $this->area . '/_parts/head.php'))
            include APP_PATH . '/View/' . $this->area . '/_parts/head.php';


        include $__filename;

        if ($includeParts && file_exists(APP_PATH . '/View/' . $this->area . '/_parts/end.php'))
            include APP_PATH . '/View/' . $this->area . '/_parts/end.php';

        return new Response(ob_get_clean());
    }

    public function dispatch($event, $eventName = null)
    {
        return $this->dispatcher->dispatch($event, $eventName);
    }

    public function renderViews(array $views, $includeParts = true)
    {

        if ($includeParts && file_exists(APP_PATH . '/View/' . $this->area . '/_parts/head.php'))
            include APP_PATH . '/View/' . $this->area . '/_parts/head.php';

        foreach ($views as $__view => $__data) {
            $__filename = sprintf(APP_PATH . '/View/' . $this->area . '/%s.php', $__view);

            if (!file_exists($__filename)) {
                /** @var ViewFileNotFoundEvent $viewFileNotFoundEvent */
                $viewFileNotFoundEvent = $this->dispatch(new ViewFileNotFoundEvent($this->request, $__filename), Events::VIEW_FILE_NOT_FOUND);
                if ($viewFileNotFoundEvent->hasResponse()) return $viewFileNotFoundEvent->getResponse();
                die;
            }

            if (is_array($__data))
                extract($__data);
            ob_start();

            include $__filename;
        }


        if ($includeParts && file_exists(APP_PATH . '/View/' . $this->area . '/_parts/end.php'))
            include APP_PATH . '/View/' . $this->area . '/_parts/end.php';

        return new Response(ob_get_clean());
    }

    public function redirectMe()
    {
        return new RedirectResponse($this->request->getBasePath() . $this->request->getPathInfo());
    }

    public function redirectReferrer()
    {
        if ($_SERVER['HTTP_REFERER'])
            return $this->redirect($_SERVER['HTTP_REFERER']);

        return $this->redirect();
    }

    public function redirect($path = '')
    {
        if (strpos($path, 'http') === false && strpos($path, '://') === false)
            $path = $this->request->getBasePath() . '/' . $path;

        return new RedirectResponse($path);
    }

    public function isPost()
    {
        return $this->request->getMethod() == 'POST';
    }

    public function _post($key = null, $default = null)
    {
        if ($key)
            return ErkinApp()->RequestPost()->get($key, $default);

        return ErkinApp()->RequestPost()->all();
    }

    public function _get($key = null, $default = null)
    {
        if ($key)
            return ErkinApp()->RequestGet()->get($key, $default);

        return ErkinApp()->RequestGet()->all();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBag
     */
    public function getFlashBag()
    {
        return $this->sessions->getFlashBag();
    }

}