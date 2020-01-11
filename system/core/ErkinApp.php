<?php

namespace ErkinApp;


use ArgumentCountError;
use Closure;
use DateTime;
use ErkinApp\Component\Config;
use ErkinApp\Component\Localization;
use ErkinApp\Controller\Controller;
use ErkinApp\Controller\IAuthController;
use ErkinApp\Event\ControllerActionEvent;
use ErkinApp\Event\ErrorEvent;
use ErkinApp\Event\Events;
use ErkinApp\Event\NotFoundEvent;
use ErkinApp\Event\RequestEvent;
use ErkinApp\Event\ResponseEvent;
use ErkinApp\Exception\ErkinAppException;
use ErkinApp\Route\AppRoute;
use ErkinApp\Route\AppRouteCollection;
use ErkinApp\Template\TemplateManager;
use Exception;
use Monolog\Logger;
use PDO;
use ReflectionException;
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
     * @var string
     */
    protected $currentArea = 'frontend';

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
     * @var AppRoute
     */
    protected $currentAppRoute;


    /**
     * ErkinApp constructor.
     */
    private function __construct()
    {
        $this->container = new Container();
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
     * @return RouteCollection
     */
    public function RouteCollection(): RouteCollection
    {
        return $this->Get(RouteCollection::class);
    }

    /**
     * @return AppRouteCollection
     */
    public function AppRoutes(): AppRouteCollection
    {
        return $this->Get(AppRouteCollection::class);
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
     * @throws ReflectionException
     */
    public function Model($class)
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
    public function DB($dbKey = 'default')
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
        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($this->RouteCollection(), $context);

        try {
            $attributes = $matcher->match($request->getPathInfo());

            /** @var RequestEvent $requestEvent */
            $requestEvent = $this->Dispatcher()->dispatch(new RequestEvent($request), Events::REQUEST);

            $controller = $attributes['controller'];
            unset($attributes['controller']);
            unset($attributes['_route']);

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
                    /** @var NotFoundEvent $notFoundEvent */
                    $notFoundEvent = $this->Dispatcher()->dispatch(new NotFoundEvent(), Events::NOT_FOUND);
                    if ($notFoundEvent->hasResponse()) {
                        return $notFoundEvent->getResponse();
                    } else {
                        throw new ErkinAppException("Action not exist : {$method}");
                    }
                }

                $ctrl_method_path = strtolower($this->getCurrentControllerShortName()) . '/' . strtolower($method);

                // Parametreleri kontrol et
                $r = new ReflectionMethod($ctrl, $method);
                $params = $r->getParameters();


                $methodParameters = [];
                foreach (array_column($params, 'name') as $paramName) {
                    if (isset($attributes[$paramName]))
                        $methodParameters[] = $attributes[$paramName];
                }


                foreach ($params as $key => $param) {
                    if (!isset($methodParameters[$key]) && !$param->isOptional()) {
                        throw new ErkinAppException($param->getName() . " is required parameter");
                    }
                }

                $this->setCurrentMethodArgs($methodParameters);

                $controllerEventName = implode('_', explode("\\", get_class($ctrl)));
                $controllerActionEventName = $controllerEventName . '::' . $method;

                $this->Dispatcher()->dispatch(new ControllerActionEvent($ctrl, $method, $methodParameters, $request), 'Application_Controller_' . $this->getCurrentArea() . '::before');
                $this->Dispatcher()->dispatch(new ControllerActionEvent($ctrl, $method, $methodParameters, $request), $controllerEventName . '::before');
                $this->Dispatcher()->dispatch(new ControllerActionEvent($ctrl, $method, $methodParameters, $request), $controllerActionEventName . '::before');

                $this->setCurrentController($ctrl);
                $this->setCurrentMethod($method);
                $this->setCurrentMethodArgs($methodParameters);

                $response = call_user_func_array(
                    [
                        $ctrl,
                        $method,
                    ],
                    $methodParameters
                );

                if (!($response instanceof Response)) {
                    if (is_array($response) || is_object($response) || is_bool($response))
                        $response = new JsonResponse($response);
                    else
                        $response = new Response($response);
                }


                $this->Dispatcher()->dispatch(new ControllerActionEvent($ctrl, $method, $methodParameters, $request, $response), $controllerActionEventName . '::after');
                $this->Dispatcher()->dispatch(new ControllerActionEvent($ctrl, $method, $methodParameters, $request, $response), $controllerEventName . '::after');
                $this->Dispatcher()->dispatch(new ControllerActionEvent($ctrl, $method, $methodParameters, $request, $response), 'Application_Controller_' . $this->getCurrentArea() . '::after');

            }

        } catch (ResourceNotFoundException $e1) {

            /** @var ErrorEvent $errorEvent */
            $errorEvent = $this->Dispatcher()->dispatch(new ErrorEvent($request, $e1), Events::ERROR);
            if ($errorEvent->hasResponse()) return $errorEvent->getResponse();
            else throw $e1;

        } catch (ArgumentCountError $e2) {

            /** @var ErrorEvent $errorEvent */
            $errorEvent = $this->Dispatcher()->dispatch(new ErrorEvent($request, $e2), Events::ERROR);
            if ($errorEvent->hasResponse()) return $errorEvent->getResponse();
            else throw $e2;

        } catch (Exception $e3) {

            /** @var ErrorEvent $errorEvent */
            $errorEvent = $this->Dispatcher()->dispatch(new ErrorEvent($request, $e3), Events::ERROR);
            if ($errorEvent->hasResponse()) return $errorEvent->getResponse();
            else throw $e3;

        }

        $this->Dispatcher()->dispatch(new ResponseEvent($request, $response), Events::RESPONSE);

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
        $this->RouteCollection()->add(
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

    /**
     * @return AppRoute
     */
    public function getCurrentAppRoute(): AppRoute
    {
        return $this->currentAppRoute;
    }

    /**
     * @param AppRoute $currentAppRoute
     * @return ErkinApp
     */
    public function setCurrentAppRoute(AppRoute $appRoute, $updateRelated = true)
    {
        $this->currentAppRoute = $appRoute;
        if ($updateRelated) {
            $this->setCurrentArea($appRoute->getArea());
            $this->setCurrentController($appRoute->getControllerClass());
            $this->setCurrentMethod($appRoute->getMethodName());
        }
    }


}