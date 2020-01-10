<?php


namespace ErkinApp\Event;


use ErkinApp\Controller\IAuthController;
use Symfony\Contracts\EventDispatcher\Event;


class CheckLoggedInStatusEvent extends Event
{

    /**
     * @var IAuthController
     */
    protected $authController;

    private $isAuthenticated = false;

    /**
     * CheckLoggedInStatusEvent constructor.
     * @param IAuthController $authController
     */
    public function __construct(IAuthController $authController)
    {
        $this->authController = $authController;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    /**
     * @param bool $isAuthenticated
     */
    public function setIsAuthenticated($isAuthenticated)
    {
        $this->isAuthenticated = $isAuthenticated;
    }

    /**
     * @return IAuthController
     */
    public function getAuthController()
    {
        return $this->authController;
    }

    public function isAreaFrontend()
    {
        return ErkinApp()->getCurrentArea() == 'Frontend';
    }

    public function isAreaBackend()
    {
        return ErkinApp()->getCurrentArea() == 'Backend';
    }

    public function isAreaApi()
    {
        return ErkinApp()->getCurrentArea() == 'Api';
    }


}