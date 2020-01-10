<?php


namespace ErkinApp;

use ErkinApp\Component\Config;
use ErkinApp\Component\Localization;
use ErkinApp\Template\TemplateManager;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Trait AppContainer
 * @package ErkinApp
 * @method Config getConfig
 * @method Localization getLocalization
 * @method EventDispatcher getDispatcher
 * @method Request getRequest
 * @method Logger getLogger
 * @method TemplateManager getTemplateManager
 * @method ParameterBag getCookies
 * @method string getArea
 * @method SessionInterface getSession
 * @method Model getModel(string $class)
 * @method Model getDb($dbKey = 'default')
 */
trait AppContainer
{
    public function __call($name, $arguments)
    {
        $name = mb_strtolower(str_replace('get', '', $name));
        switch ($name) {
            case 'config':
                return ErkinApp()->Config();
            case 'localization':
                return ErkinApp()->Localization();
            case 'dispatcher':
                return ErkinApp()->Dispatcher();
            case 'request':
                return ErkinApp()->Request();
            case 'logger':
                return ErkinApp()->Logger();
            case 'templatemanager':
                return ErkinApp()->TemplateManager();
            case 'cookies':
                return ErkinApp()->Cookies();
            case 'session':
                return ErkinApp()->Session();
            case 'model':
                return ErkinApp()->Model($arguments[0]);
            case 'db':
                return isset($arguments[0]) ? ErkinApp()->DB($arguments[0]) : ErkinApp()->DB();
            case 'area':
                return ErkinApp()->getCurrentArea();
        }
        return ErkinApp()->Get($name);
    }
}