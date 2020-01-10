<?php


namespace ErkinApp\Controller;


use ErkinApp\Event\CheckLoggedInStatusEvent;
use ErkinApp\Event\Events;


class FrontendAuthController extends Controller implements IAuthController
{
    public $user;

    /**
     * FrontendAuthController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->user = ErkinApp()->Session()->get(SESSION_FRONTEND_AUTH);
    }

    function isLoggedIn()
    {
        $isAuthenticated = ErkinApp()->Session()->get(SESSION_FRONTEND_AUTH) !== null;

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
        return $this->redirect(FRONTEND_LOGIN_PATH);
    }

    function isLoginPage()
    {
        return strpos(strtolower($this->getRequest()->getPathInfo()), ('/' . strtolower(FRONTEND_LOGIN_PATH))) === 0;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }


}