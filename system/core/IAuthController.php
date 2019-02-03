<?php
/**
 * Created by PhpStorm.
 * User: erkin
 * Date: 20.11.2018
 * Time: 22:43
 */

namespace ErkinApp;


interface IAuthController
{

    function isLoggedIn();

    function goToLogin();

    function isLoginPage();
}