<?php

namespace ErkinApp\Controller;


use ErkinApp\AppContainer;
use ErkinApp\ErkinApp;
use ErkinApp\Exception\ErkinAppException;
use ErkinApp\Model;
use Exception;
use PDO;
use ReflectionException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Contracts\EventDispatcher\Event;
use function ErkinApp\Helpers\getClassShortName;

/**
 * Class Controller
 * @package ErkinApp
 */
abstract class Controller
{
    use AppContainer;

    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        ErkinApp::getInstance()->setCurrentArea(explode('\\', get_called_class())[2]);
    }
    
    /**
     * @param string $__view
     * @param array $__data
     * @return Response
     */
    public function renderViewPlain($__view = '', $__data = [])
    {
        return $this->renderView($__view, $__data, false);
    }

    /**
     * @param string $filename
     * @param array $data
     * @param bool $includeParts
     * @return Response
     */
    public function renderView($filename = '', $data = [], $includeParts = true)
    {
        if (is_array($filename)) {
            $data = $filename;
            $filename = '';
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
        if (!$filename) {
            $__called_controller_short_name = strtolower(getClassShortName(get_class(debug_backtrace()[1]['object'])));
            $filename = $__called_controller_short_name . '/' . debug_backtrace()[1]['function'];
            if (strpos($filename, 'renderViewPlain') !== false)
                $filename = $__called_controller_short_name . '/' . debug_backtrace()[2]['function'];
        }

        return ErkinApp()->TemplateManager()->render($filename, $data, $includeParts);
    }

    /**
     * @param $event
     * @param null $eventName
     * @return mixed|object|Event|null
     * @throws Exception
     */
    public function dispatch($event, $eventName = null)
    {
        return ErkinApp()->Dispatcher()->dispatch($event, $eventName);
    }

    /**
     * @return RedirectResponse
     * @throws Exception
     */
    public function redirectMe()
    {
        return new RedirectResponse(ErkinApp()->Request()->getBasePath() . ErkinApp()->Request()->getPathInfo());
    }

    /**
     * @return RedirectResponse
     * @throws Exception
     */
    public function redirectReferrer()
    {
        if ($_SERVER['HTTP_REFERER'])
            return $this->redirect($_SERVER['HTTP_REFERER']);

        return $this->redirect();
    }

    /**
     * @param string $path
     * @return RedirectResponse
     * @throws Exception
     */
    public function redirect($path = '')
    {
        if (strpos($path, 'http') === false && strpos($path, '://') === false)
            $path = ErkinApp()->Request()->getBasePath() . '/' . $path;

        return new RedirectResponse($path);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isPost()
    {
        return ErkinApp()->Request()->getMethod() == 'POST';
    }

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed
     * @throws Exception
     */
    public function _post($key = null, $default = null)
    {
        if ($key)
            return ErkinApp()->RequestPost()->get($key, $default);

        return ErkinApp()->RequestPost()->all();
    }

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed
     * @throws Exception
     */
    public function _get($key = null, $default = null)
    {
        if ($key)
            return ErkinApp()->RequestGet()->get($key, $default);

        return ErkinApp()->RequestGet()->all();
    }

    /**
     * @return FlashBag
     * @throws Exception
     */
    public function getFlashBag()
    {
        return ErkinApp()->Session()->getFlashBag();
    }

}