<?php


namespace ErkinApp\Controller;


use ErkinApp\Events\CheckLoggedInStatusEvent;
use ErkinApp\Events\Events;


class BackendAuthController extends Controller implements IAuthController
{


    public function __construct()
    {
        parent::__construct();
    }

    function isLoggedIn()
    {
        $isAuthenticated = ErkinApp()->Session()->get(SESSION_BACKEND_AUTH) !== null;

        // if session is not set, check the remember me cookie
        if (!$isAuthenticated) {
            /** @var CheckLoggedInStatusEvent $event */
            $event = ErkinApp()->Dispatcher()->dispatch(new CheckLoggedInStatusEvent($this), Events::CHECK_LOGGED_IN_STATUS);
            $isAuthenticated = $event->isAuthenticated();
        }

        return $isAuthenticated;
    }

    function goToLogin()
    {
        return $this->redirect(BACKEND_LOGIN_PATH);
    }

    function isLoginPage()
    {
        return strpos(strtolower($this->request->getPathInfo()), ('/' . strtolower(BACKEND_LOGIN_PATH))) === 0;
    }
}