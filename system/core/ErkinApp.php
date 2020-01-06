<?php

namespace ErkinApp;


use ArgumentCountError;
use Closure;
use DateTime;
use ErkinApp\Components\Config;
use ErkinApp\Components\Localization;
use ErkinApp\Controller\Controller;
use ErkinApp\Controller\IAuthController;
use ErkinApp\Events\ActionNotFoundEvent;
use ErkinApp\Events\ControllerActionEvent;
use ErkinApp\Events\ErrorEvent;
use ErkinApp\Events\Events;
use ErkinApp\Events\RequestEvent;
use ErkinApp\Events\ResponseEvent;
use ErkinApp\Exceptions\ErkinAppException;
use ErkinApp\Template\TemplateManager;
use Exception;
use Monolog\Logger;
use PDO;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Contracts\EventDispatcher\Event;
use function ErkinApp\Helpers\getClassShortName;

class ErkinApp implements HttpKernelInterface
{
    /**
     * @var ErkinApp
     */
    private static $instance;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var string
     */
    protected $currentArea;

    /**
     * @var Controller
     */
    protected $currentController;

    /**
     * @var string
     */
    protected $currentMethod;

    /**
     * @var array
     */
    protected $currentMethodArgs;


    /**
     * ErkinApp constructor.
     */
    private function __construct()
    {
        $this->container = new Container();
        $this->routes = new RouteCollection();
    }

    /**
     * @return ErkinApp
     */
    public static function getInstance()
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return Container
     */
    public function Container()
    {
        return $this->container;
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function Get($name)
    {
        return $this->Container()->get($name);
    }

    /**
     * @return Request
     * @throws Exception
     */
    public function Request()
    {
        return $this->Get(Request::class);
    }

    /**
     * @return ParameterBag
     * @throws Exception
     */
    public function RequestGet()
    {
        return $this->Request()->query;
    }

    /**
     * @return ParameterBag
     * @throws Exception
     */
    public function RequestPost()
    {
        return $this->Request()->request;
    }

    /**
     * @param string $key
     * @return bool|mixed
     * @throws Exception
     */
    public function UserFrontend($key = '')
    {
        return $this->getUserInfo($this->Session()->get(SESSION_FRONTEND_AUTH), $key);
    }

    /**
     * @param string $key
     * @return bool|mixed
     * @throws Exception
     */
    public function UserBackend($key = '')
    {
        return $this->getUserInfo($this->Session()->get(SESSION_BACKEND_AUTH), $key);
    }

    /**
     * @param string $key
     * @return bool|mixed
     * @throws Exception
     */
    public function UserApi($key = '')
    {
        return $this->getUserInfo($this->Session()->get(SESSION_API_AUTH), $key);
    }

    /**
     * @return RouteCollection
     */
    public function Routes(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * @return Logger
     * @throws Exception
     */
    public function Logger()
    {
        return $this->Get(Logger::class);
    }

    /**
     * @return Config
     * @throws Exception
     */
    public function Config()
    {
        return $this->Get(Config::class);
    }

    /**
     * @return Localization
     * @throws Exception
     */
    public function Localization()
    {
        return $this->Get(Localization::class);
    }

    /**
     * @return SessionInterface|null
     * @throws Exception
     */
    public function Session()
    {
        return $this->Request()->getSession();
    }

    /**
     * @return ParameterBag
     * @throws Exception
     */
    public function Cookies()
    {
        return $this->Request()->cookies;
    }

    /**
     * @return EventDispatcher
     * @throws Exception
     */
    public function Dispatcher()
    {
        return $this->Get(EventDispatcher::class);
    }

    /**
     * @param string $class
     * @return Model
     * @throws ErkinAppException
     * @throws \ReflectionException
     */
    public function Models($class)
    {
        if (!class_exists($class))
            throw new ErkinAppException("Model class not found");
        return $this->Container()->maybeBorn($class);
    }

    /**
     * @param $dbKey
     * @return PDO|null
     * @throws Exception
     */
    public function DB($dbKey)
    {
        $id = "db.$dbKey";
        if (!$this->container->has($id)) {
            $dsn = $this->Config()->get("$id.dsn");
            $host = $this->Config()->get("$id.host");
            $port = $this->Config()->get("$id.port");
            $username = $this->Config()->get("$id.username");
            $password = $this->Config()->get("$id.password");
            $dbname = $this->Config()->get("$id.dbname");

            if (!$dsn) {
                $dsn = "mysql:dbname=$dbname;host=$host;port=$port;";
            }
            $this->container->offsetSet($id, $this->loadPDO($dsn, $username, $password));
        }

        return $this->container->offsetGet($id);
    }

    /**
     * @return TemplateManager
     * @throws Exception
     */
    public function TemplateManager()
    {
        return $this->Get(TemplateManager::class);
    }

    /**
     * @param $user
     * @param string $key
     * @return bool|mixed
     */
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
     * @param Request $request
     * @param int $type
     * @param bool $catch
     * @return mixed|JsonResponse|Response
     * @throws Exception
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
//        $this->Container()->offsetSet(Request::class, $request);

        // create a context using the current request
        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($this->routes, $context);

        try {
            $attributes = $matcher->match($request->getPathInfo());

            /** @var RequestEvent $requestEvent */
            $requestEvent = $this->Dispatcher()->dispatch(new RequestEvent($request), Events::REQUEST);

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
                    /** @var ActionNotFoundEvent $actionNotFoundEvent */
                    $actionNotFoundEvent = $this->Dispatcher()->dispatch(new ActionNotFoundEvent($request), Events::ACTION_NOT_FOUND);
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

                    if (strpos(strtolower($request->getPathInfo()), '/frontend') === 0 ||
                        strpos(strtolower($request->getPathInfo()), '/' . BACKEND_AREA_NAME) === 0 ||
                        strpos(strtolower($request->getPathInfo()), '/' . API_AREA_NAME) === 0) {
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

                $this->Dispatcher()->dispatch(new ControllerActionEvent($ctrl, $method, $method_parameters, $request), 'Application_Controller_' . $this->getCurrentArea() . '::before');
                $this->Dispatcher()->dispatch(new ControllerActionEvent($ctrl, $method, $method_parameters, $request), $controllerActionEventName . '::before');

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


                $this->Dispatcher()->dispatch(new ControllerActionEvent($ctrl, $method, $method_parameters, $request, $response), 'Application_Controller_' . $this->currentArea . '::after');
                $this->Dispatcher()->dispatch(new ControllerActionEvent($ctrl, $method, $method_parameters, $request, $response), $controllerActionEventName . '::after');


            }

        } catch (ResourceNotFoundException $e1) {

//            $response = new Response('An error occurred ResourceNotFoundException: ' . $e1->getMessage(), Response::HTTP_NOT_FOUND);
            /** @var ErrorEvent $errorEvent */
            $errorEvent = $this->Dispatcher()->dispatch(new ErrorEvent($request, $e1), Events::ERROR);
            if ($errorEvent->hasResponse()) return $errorEvent->getResponse();
            else throw $e1;

        } catch (ArgumentCountError $e2) {
//            throw $e2;
//            $response = new Response('An error occurred ArgumentCountError: ' . $e2->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            /** @var ErrorEvent $errorEvent */
            $errorEvent = $this->Dispatcher()->dispatch(new ErrorEvent($request, $e2), Events::ERROR);
            if ($errorEvent->hasResponse()) return $errorEvent->getResponse();
            else throw $e2;

        } catch (Exception $e3) {
//            throw $e2;
//            $response = new Response('An error occurred ' . get_class_short_name($e3) . ': ' . $e3->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            /** @var ErrorEvent $errorEvent */
            $errorEvent = $this->Dispatcher()->dispatch(new ErrorEvent($request, $e3), Events::ERROR);
            if ($errorEvent->hasResponse()) return $errorEvent->getResponse();
            else throw $e3;

        }


        $this->Dispatcher()->dispatch(new ResponseEvent($response, $request), Events::RESPONSE);

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
     * @throws Exception
     */
    public function on($event, $callback)
    {
        $this->Dispatcher()->addListener($event, $callback);
    }

    /**
     * @param $event
     * @return mixed|object|Event|null
     * @throws Exception
     */
    public function fire($event)
    {
        return $this->Dispatcher()->dispatch($event);
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