<?php

namespace ErkinApp;


use ArgumentCountError;
use Closure;
use DateTime;
use ErkinApp\Components\Config;
use ErkinApp\Events\ActionNotFoundEvent;
use ErkinApp\Events\ControllerActionEvent;
use ErkinApp\Events\ErrorEvent;
use ErkinApp\Events\Events;
use ErkinApp\Events\RequestEvent;
use ErkinApp\Events\ResponseEvent;
use ErkinApp\Exceptions\ErkinAppException;
use Exception;
use PDO;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use function ErkinApp\Helpers\getClassShortName;
use function ErkinApp\Helpers\loadDefaultLanguage;
use function ErkinApp\Helpers\loadLanguage;

class ErkinApp implements HttpKernelInterface
{

    /**
     * @var ErkinApp
     */
    private static $instance;

    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Container
     */
    protected $container;


    protected $currentArea;
    protected $currentController;
    protected $currentMethod;
    protected $currentMethodArgs;
    protected $languages;

    /** @var Config */
    private $config;

    /**
     * ErkinApp constructor.
     * @throws ErkinAppException
     */
    private function __construct()
    {
        $this->container = new Container();
        $this->routes = new RouteCollection();
        $this->dispatcher = new EventDispatcher();
        $session = new Session();

        $this->config = $this->loadConfig();
        $this->addConfigDotNotationToContainer();

        $this->request = Request::createFromGlobals();
        $this->request->setSession($session);

        if (defined('DEFAULT_LANGUAGE')) {
            $this->languages[DEFAULT_LANGUAGE] = loadDefaultLanguage();
        }

    }

    /**
     * @return ErkinApp
     * @throws ErkinAppException
     */
    public static function getInstance()
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return ParameterBag
     */
    public function RequestGet()
    {
        return $this->Request()->query;
    }

    /**
     * @return Request
     */
    public function Request()
    {
        if ($this->request == null) {
            $this->request = Request::createFromGlobals();
        }
        return $this->request;
    }

    /**
     * @return ParameterBag
     */
    public function RequestPost()
    {
        return $this->Request()->request;
    }

    private function getUserInfo($user, $key = '')
    {
        if (is_array($user) && $key) {
            if (isset($user[$key])) return $user[$key];
            return false;
        } elseif (is_object($user) && $key) {
            if (isset($user->$key)) return $user->$key;
            return false;
        }
        if (!$user) return false;

        return $user;
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function UserFrontend($key = '')
    {
        return $this->getUserInfo($this->request->getSession()->get(SESSION_FRONTEND_AUTH), $key);
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function UserBackend($key = '')
    {
        return $this->getUserInfo($this->request->getSession()->get(SESSION_BACKEND_AUTH), $key);
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function UserApi($key = '')
    {
        return $this->getUserInfo($this->request->getSession()->get(SESSION_API_AUTH), $key);
    }

    /**
     * @param null $key
     * @param null $lang
     * @return bool
     */
    public function Language($key = null, $lang = null)
    {
        if ($lang === null)
            if (defined('DEFAULT_LANGUAGE')) $lang = DEFAULT_LANGUAGE;
            else return false;

        if (!isset($this->languages[$lang]))
            $this->languages[$lang] = loadLanguage($lang);

        if ($key)
            return isset($this->languages[$lang][$key]) ? $this->languages[$lang][$key] : false;
        else
            return $this->languages[$lang];
    }

    /**
     * @return RouteCollection
     */
    public function Routes(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * @return bool|Container|Model|Model[]|mixed|EventDispatcher|ParameterBag|Request|SessionInterface|null
     * @throws Exception
     */
    public function Logger()
    {
        return $this->Get('logger');
    }

    /**
     * @param $name
     * @return bool|Model|Model[]|mixed|Container|EventDispatcher|ParameterBag|Request|SessionInterface|null
     * @throws Exception
     */
    public function Get($name)
    {
        switch ($name) {
            case 'sessions':
                return $this->Session();
            case 'cookies':
                return $this->Request()->cookies;
            case 'request':
                return $this->Request();
            case 'dispatcher':
                return $this->Dispatcher();
            case 'area':
                return $this->getCurrentArea();
            case 'db':
                return $this->DB('default');
            case 'container':
                return $this->Container();
        }

        if (!$this->container->offsetExists($name) && class_exists($name)) {
            $this->container->offsetSet($name, new $name());
        }

        return $this->Container()->get($name);
    }

    /**
     * @return SessionInterface|null
     */
    public function Session()
    {
        return $this->request->getSession();
    }

    /**
     * @return EventDispatcher
     */
    public function Dispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param string $class
     * @return Model
     * @throws ErkinAppException
     */
    public function Models($class)
    {
        if (!class_exists($class))
            throw new ErkinAppException("Model class not found");
        return $this->container->get($class);
    }

    /**
     * @return mixed
     * @throws ErkinAppException
     */
    private function loadConfig()
    {
        $configFilePath = SYS_PATH . '/config/config.php';
        if (!file_exists($configFilePath))
            throw new ErkinAppException("Config file not found");

        $config = include $configFilePath;

        $this->container->offsetSet('config', $config);

        return new Config($config);
    }

    private function addConfigDotNotationToContainer()
    {
        $configItems = $this->config->getAsDotNotation('config.');
        foreach ($configItems as $key => $value) {
            $this->container->offsetSet($key, $value);
        }
    }

    /**
     * @param $dsn
     * @param $username
     * @param $password
     * @return PDO
     * @throws Exception
     */
    public function loadPDO($dsn, $username, $password)
    {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $offset = (new DateTime())->format("P");
        $pdo->exec("SET NAMES 'utf8mb4'; SET CHARSET 'utf8'; SET time_zone='$offset';");
        return $pdo;
    }

    /**
     * @return mixed
     */
    public function getCurrentArea()
    {
        return $this->currentArea;
    }

    /**
     * @param mixed $currentArea
     */
    public function setCurrentArea($currentArea)
    {
        $this->currentArea = $currentArea;
    }

    /**
     * @param $dbKey
     * @return PDO|null
     * @throws Exception
     */
    public function DB($dbKey)
    {
        $id = "db.$dbKey";
        if ($dbKey == 'default') $id = 'db';
        if (!$this->container->offsetGet($id)) {
            $dsn = $this->config->get('db.dsn');
            $host = $this->config->get('db.host');
            $port = $this->config->get('db.port');
            $username = $this->config->get('db.username');
            $password = $this->config->get('db.password');
            $dbname = $this->config->get('db.dbname');

            if (!$dsn) {
                $dsn = "mysql:dbname=$dbname;host=$host;port=$port;";
            }

            $this->container->offsetSet($id, $this->loadPDO($dsn, $username, $password));
        }

        return $this->container->offsetGet($id);
    }

    /**
     * @return Container
     */
    public function Container()
    {
        return $this->container;
    }

    /**
     * @param Request $request
     * @param int $type
     * @param bool $catch
     * @return mixed|JsonResponse|Response
     * @throws Exception
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {

        $this->request = $request;

        // create a context using the current request
        $context = new RequestContext();
        $context->fromRequest($this->request);

        $matcher = new UrlMatcher($this->routes, $context);

        try {
            $attributes = $matcher->match($this->request->getPathInfo());

            /** @var RequestEvent $requestEvent */
            $requestEvent = $this->dispatcher->dispatch(new RequestEvent($this->request), Events::REQUEST);

            $controller = $attributes['controller'];

            unset($attributes['controller']);

            if ($requestEvent->hasResponse()) {
                $response = $requestEvent->getResponse();
            } else if ($controller instanceof Closure) {
                $response = call_user_func_array($controller, $attributes);
            } else {

                /** @var Controller $ctrl */
                $ctrl = new $controller[0]();

                if ($ctrl instanceof IAuthController) {
                    if (!$ctrl->isLoggedIn() && !$ctrl->isLoginPage()) {
                        return $ctrl->goToLogin();
                    }
                }

                $method = isset($controller[1]) ? $controller[1] : 'index';

                if (!method_exists($ctrl, $method)) {
                    $actionNotFoundEvent = $this->dispatcher->dispatch(new ActionNotFoundEvent($request), Events::ACTION_NOT_FOUND);
                    if ($actionNotFoundEvent->hasResponse()) {
                        return $actionNotFoundEvent->getResponse();
                    } else {
                        throw new ErkinAppException("Action not exist : {$method}");
                    }
                }

                $ctrl_method_path = strtolower($this->getCurrentControllerShortName()) . '/' . strtolower($method);

                // Parametreleri kontrol et
                $r = new ReflectionMethod($ctrl, $method);
                $params = $r->getParameters();

                /*
                 * If default dynamic routing, route and controller/method strings are similar
                 */
                if (strpos(strtolower($attributes['_route']), strtolower($ctrl_method_path)) !== false) {

                    if (strpos(strtolower($this->request->getPathInfo()), '/frontend') === 0 ||
                        strpos(strtolower($this->request->getPathInfo()), '/' . BACKEND_AREA_NAME) === 0 ||
                        strpos(strtolower($this->request->getPathInfo()), '/' . API_AREA_NAME) === 0) {
                        $method_parameters = array_slice(explode('/', $attributes['_route']), 4);
                    } else {
                        $method_parameters = array_slice(explode('/', $attributes['_route']), 3);
                    }

                } /*
                 * If not,
                 * Custom routing must be handle
                 * Controller method parameters must be handle properly
                 */
                else {
                    $method_parameters = [];
                    foreach (array_column($params, 'name') as $paramName) {
                        if (isset($attributes[$paramName]))
                            $method_parameters[] = $attributes[$paramName];
                    }
                }

                foreach ($params as $key => $param) {
                    if (!isset($method_parameters[$key]) && !$param->isOptional()) {
                        throw new ErkinAppException($param->getName() . " is required parameter");
                    }
                }

                $this->setCurrentMethodArgs($method_parameters);

                $controllerActionEventName = implode('_', explode("\\", get_class($ctrl))) . '::' . $method;

                $this->dispatcher->dispatch(new ControllerActionEvent($ctrl, $method, $method_parameters, $request), 'Application_Controller_' . $this->getCurrentArea() . '::before');
                $this->dispatcher->dispatch(new ControllerActionEvent($ctrl, $method, $method_parameters, $request), $controllerActionEventName . '::before');

                $this->setCurrentController($ctrl);
                $this->setCurrentMethod($method);
                $this->setCurrentMethodArgs($method_parameters);

                $response = call_user_func_array(
                    [
                        $ctrl,
                        $method,
                    ],
                    $method_parameters
                );

                if (!($response instanceof Response)) {
                    if (is_array($response) || is_object($response) || is_bool($response))
                        $response = new JsonResponse($response);
                    else
                        $response = new Response($response);
                }


                $this->dispatcher->dispatch(new ControllerActionEvent($ctrl, $method, $method_parameters, $request, $response), 'Application_Controller_' . $this->currentArea . '::after');
                $this->dispatcher->dispatch(new ControllerActionEvent($ctrl, $method, $method_parameters, $request, $response), $controllerActionEventName . '::after');


            }

        } catch (ResourceNotFoundException $e1) {

//            $response = new Response('An error occurred ResourceNotFoundException: ' . $e1->getMessage(), Response::HTTP_NOT_FOUND);
            $errorEvent = $this->dispatcher->dispatch(new ErrorEvent($request, $e1), Events::ERROR);
            if ($errorEvent->hasResponse()) return $errorEvent->getResponse();
            else throw $e1;

        } catch (ArgumentCountError $e2) {
//            throw $e2;
//            $response = new Response('An error occurred ArgumentCountError: ' . $e2->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            $errorEvent = $this->dispatcher->dispatch(new ErrorEvent($request, $e2), Events::ERROR);
            if ($errorEvent->hasResponse()) return $errorEvent->getResponse();
            else throw $e2;

        } catch (Exception $e3) {
//            throw $e2;
//            $response = new Response('An error occurred ' . get_class_short_name($e3) . ': ' . $e3->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            $errorEvent = $this->dispatcher->dispatch(new ErrorEvent($request, $e3), Events::ERROR);
            if ($errorEvent->hasResponse()) return $errorEvent->getResponse();
            else throw $e3;

        }


        $this->dispatcher->dispatch(new ResponseEvent($response, $this->request), Events::RESPONSE);

        return $response;
    }

    /**
     * @return string
     */
    public function getCurrentControllerShortName()
    {
        try {
            return getClassShortName($this->currentController);
        } catch (Exception $e) {
            return $this->currentController;
        }
    }

    /**
     * @param $path
     * @param $controller
     * @param array $requirements
     * @param array $options
     * @param string|null $host
     * @param array $schemes
     * @param array $methods
     * @param string|null $condition
     */
    public function map($path, $controller, array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        $this->routes->add(
            $path,
            new Route(
                $path,
                array('controller' => $controller),
                $requirements,
                $options,
                $host,
                $schemes,
                $methods,
                $condition
            )
        );
    }

    /**
     * @param $event
     * @param $callback
     */
    public function on($event, $callback)
    {
        $this->dispatcher->addListener($event, $callback);
    }

    /**
     * @param $event
     * @return mixed|object|Event|null
     */
    public function fire($event)
    {
        return $this->dispatcher->dispatch($event);
    }

    /**
     * @return mixed
     */
    public function getCurrentMethodArgs()
    {
        return $this->currentMethodArgs;
    }

    /**
     * @param mixed $currentMethodArgs
     */
    public function setCurrentMethodArgs($currentMethodArgs)
    {
        $this->currentMethodArgs = $currentMethodArgs;
    }

    /**
     * @return string
     */
    public function getCurrentActionMethodPath()
    {
        if ($this->getCurrentArea() == 'Frontend')
            return strtolower(getClassShortName($this->currentController)) . '/' . $this->getCurrentMethod();
        else
            return strtolower($this->getCurrentArea()) . '/' . strtolower(getClassShortName($this->currentController)) . '/' . $this->getCurrentMethod();
    }

    /**
     * @return mixed
     */
    public function getCurrentMethod()
    {
        return $this->currentMethod;
    }

    /**
     * @param mixed $currentMethod
     */
    public function setCurrentMethod($currentMethod)
    {
        $this->currentMethod = $currentMethod;
    }

    /**
     * @return string
     */
    function getCurrentControllerPath()
    {
        if ($this->getCurrentArea() == 'Frontend')
            return strtolower(getClassShortName($this->getCurrentController()));
        else
            return strtolower($this->getCurrentArea()) . '/' . strtolower(getClassShortName($this->getCurrentController()));
    }

    /**
     * @return mixed
     */
    public function getCurrentController()
    {
        return $this->currentController;
    }

    /**
     * @param mixed $currentController
     */
    public function setCurrentController($currentController)
    {
        $this->currentController = $currentController;
    }


}