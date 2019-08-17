<?php

namespace ErkinApp;


use Envms\FluentPDO\Query;
use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use function ErkinApp\Helpers\ErkinApp;
use function ErkinApp\Helpers\get_class_short_name;
use function ErkinApp\Helpers\split_camel_case;

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
 */
abstract class Controller
{

    /**
     * @var Model
     */
    protected $model;


    /**
     * BaseController constructor.
     * @param Request $request
     */
    public function __construct()
    {

        $this->area = explode('\\', get_called_class())[2];

        // Controller'ın kendi modelini yükle
        $modelclass = str_replace('\\Controller\\', '/Model/', get_called_class());
        $modelclass = str_replace('/', '\\', $modelclass);

        $this->model = ErkinApp()->Models($modelclass);
    }

    public function __get($name)
    {

        switch ($name) {
            case 'sessions':
                return ErkinApp()->Session();
            case 'cookies':
                return ErkinApp()->Request()->cookies;
            case 'request':
                return ErkinApp()->Request();
            case 'dispatcher':
                return ErkinApp()->Dispatcher();
            case 'models':
                return ErkinApp()->Models();
            case 'area':
                return ErkinApp()->getCurrentArea();
            case 'db':
                return ErkinApp()->DB('default');
            case 'container':
                return ErkinApp()->Container();
        }


        /*
         * Dynamically access models
         *
         *
         * $this->modelAccount   => return Application\Model\{area-controller-in}\Account
         * $this->modelFrontendAccount   => return Application\Model\Frontend\Account
         * $this->modelBackendAccount   => return Application\Model\Backend\Account
         */
        if (strpos($name, 'model') === 0) {

            $parts = split_camel_case($name);
            if (count($parts) == 2) {
                $parts[2] = $parts[1]; // Slide forward model name
                $parts[1] = ucfirst($this->area); // add own area name before model name
            }
            $modelname = implode('\\', array_slice($parts, 1, 1)) . '\\' . implode('', array_slice($parts, 2));


            $modelclass = 'Application\\Model\\' . $modelname;

            return ErkinApp()->Models($modelclass);
        }

        return ErkinApp()->Container()->offsetGet($name);
    }


    /**
     * @return Model[]
     */
    public function getModels()
    {
        return ErkinApp()->Models();
    }

    /**
     * @param string $class
     * @return bool|Model
     */
    public function getModel($class = '')
    {
        if (!$class) return $this->model;
        return ErkinApp()->Models($class);
    }

    public function getDb($dbkey = 'default')
    {
        return ErkinApp()->DB($dbkey);
    }

    public function dispatch($eventName)
    {
        return $this->dispatcher->dispatch($eventName);
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
            $__called_controller_short_name = strtolower(get_class_short_name(get_class(debug_backtrace()[1]['object'])));
            $__view = $__called_controller_short_name . '/' . debug_backtrace()[1]['function'];
            if (strpos($__view, 'renderViewPlain') !== false)
                $__view = $__called_controller_short_name . '/' . debug_backtrace()[2]['function'];
        }

        $__filename = sprintf(APP_PATH . '/View/' . $this->area . '/%s.php', $__view);

        if (!file_exists($__filename)) {
            return new Response("View file not found : <strong>{$__filename}</strong>");
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

    public function renderViews(array $views, $includeParts = true)
    {

        if ($includeParts && file_exists(APP_PATH . '/View/' . $this->area . '/_parts/head.php'))
            include APP_PATH . '/View/' . $this->area . '/_parts/head.php';

        foreach ($views as $__view => $__data) {
            $__filename = sprintf(APP_PATH . '/View/' . $this->area . '/%s.php', $__view);

            if (!file_exists($__filename)) {
                return new Response("View file not found : <strong>{$__filename}</strong>");
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