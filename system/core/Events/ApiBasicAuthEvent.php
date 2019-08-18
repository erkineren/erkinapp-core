<?php


namespace ErkinApp\Events;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;


class ApiBasicAuthEvent extends Event
{
    private $auth_user;
    private $auth_pw;
    private $auth_digest;

    private $response;
    private $isAuthenticated = false;

    /**
     * ApiBasicAuthEvent constructor.
     */
    public function __construct($auth_user, $auth_pw = '', $auth_digest = '')
    {
        $this->auth_user = $auth_user;
        $this->auth_pw = $auth_pw;
        $this->auth_digest = $auth_digest;
    }

    /**
     * @return mixed
     */
    public function getAuthPw()
    {
        return $this->auth_pw;
    }

    /**
     * @return mixed
     */
    public function getAuthDigest()
    {
        return $this->auth_digest;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function isResponse()
    {
        return $this->response !== null;
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
    public function setAuthenticated($isAuthenticated)
    {
        $this->isAuthenticated = $isAuthenticated;

        if ($isAuthenticated) {
            ErkinApp()->Session()->set(SESSION_API_AUTH, $this->getAuthUser());
        } else  ErkinApp()->Session()->remove(SESSION_API_AUTH);

    }

    /**
     * @return mixed
     */
    public function getAuthUser()
    {
        return $this->auth_user;
    }


}