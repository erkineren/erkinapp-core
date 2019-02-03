<?php

use ErkinApp\ErkinApp;

/**
 * @return ErkinApp
 */
function ErkinApp()
{
    return ErkinApp::getInstance();
}

/**
 * @param string $key
 * @return bool|mixed
 */
function getUserFrontend($key = '')
{
    return ErkinApp()->UserFrontend($key);
}

/**
 * @param string $key
 * @return bool|mixed
 */
function getUserBackend($key = '')
{
    return ErkinApp()->UserBackend($key);
}

/**
 * @param string $key
 * @return bool|mixed
 */
function getUserApi($key = '')
{
    return ErkinApp()->UserApi($key);
}


