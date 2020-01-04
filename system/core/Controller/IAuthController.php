<?php


namespace ErkinApp\Controller;


interface IAuthController
{
    function isLoggedIn();

    function goToLogin();

    function isLoginPage();
}