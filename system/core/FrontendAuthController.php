<?php


namespace ErkinApp;


use ErkinApp\Events\CheckLoggedInStatusEvent;
use ErkinApp\Events\Events;
use function ErkinApp\Helpers\ErkinApp;

class FrontendAuthController extends Controller implements IAuthController
{

    public $user;

    /**
     * FrontendAuthController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->user = $this->sessions->get(SESSION_FRONTEND_AUTH);
    }

    function isLoggedIn()
    {
        $isAuthenticated = ErkinApp()->Session()->get(SESSION_FRONTEND_AUTH) !== null;

        // if session is not set, check the remember me cookie
        if (!$isAuthenticated) {
            /** @var CheckLoggedInStatusEvent $event */
            $event = ErkinApp()->Dispatcher()->dispatch(Events::CHECK_LOGGED_IN_STATUS, new CheckLoggedInStatusEvent($this));
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
        return strpos(strtolower($this->request->getPathInfo()), ('/' . strtolower(FRONTEND_LOGIN_PATH))) === 0;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }


}