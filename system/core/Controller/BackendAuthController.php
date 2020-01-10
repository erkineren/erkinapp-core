<?php


namespace ErkinApp\Controller;


use ErkinApp\Event\CheckLoggedInStatusEvent;
use ErkinApp\Event\Events;


class BackendAuthController extends Controller implements IAuthController
{
    public function __construct()
    {
        parent::__construct();
    }

    function isLoggedIn()
    {
        $isAuthenticated = $this->getSession()->get(SESSION_BACKEND_AUTH) !== null;

        // if session is not set, check the remember me cookie
        if (!$isAuthenticated) {
            /** @var CheckLoggedInStatusEvent $event */
            $event = $this->getDispatcher()->dispatch(new CheckLoggedInStatusEvent($this), Events::CHECK_LOGGED_IN_STATUS);
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
        return strpos(strtolower($this->getRequest()->getPathInfo()), ('/' . strtolower(BACKEND_LOGIN_PATH))) === 0;
    }
}