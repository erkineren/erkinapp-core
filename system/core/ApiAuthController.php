<?php


namespace ErkinApp;


use ErkinApp\Events\ApiBasicAuthEvent;
use ErkinApp\Events\Events;
use ErkinApp\Responses\AjaxResponse;
use ErkinApp\Responses\PrettyJsonResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthController extends Controller implements IAuthController
{


    public function __construct()
    {
        parent::__construct();
    }

    function isLoggedIn()
    {
        $auth = $this->request->headers->get('authorization');
        $credentials = base64_decode(substr($auth, 6));

        $credentials = explode(':', $credentials);

        if (count($credentials) != 2) return false;

        /** @var ApiBasicAuthEvent $event */
        $event = $this->dispatcher->dispatch(Events::API_BASIC_AUTHENTICATION,
            new ApiBasicAuthEvent($credentials[0], $credentials[1]));


        return $event->isAuthenticated();
    }

    function goToLogin()
    {
        return AjaxResponse::Error('API credentials are invalid.');
    }

    public function authenticate()
    {
        header('WWW-Authenticate: Basic realm="Prompt Login"');
        header('HTTP/1.0 401 Unauthorized');
        header('Content-Type: application/json');

        return AjaxResponse::Error('API user credentials are invalid.');
        exit;
    }


    function isLoginPage()
    {
        return false;
    }
}