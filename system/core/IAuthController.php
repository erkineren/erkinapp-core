<?php


namespace ErkinApp;


interface IAuthController
{

    function isLoggedIn();

    function goToLogin();

    function isLoginPage();
}