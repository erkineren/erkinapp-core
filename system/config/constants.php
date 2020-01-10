<?php
if (!defined('APP_PATH'))
    define('APP_PATH', realpath(BASE_PATH . '/app'));

if (!defined('LANGUAGE_PATH'))
    define('LANGUAGE_PATH', realpath(BASE_PATH . '/languages'));

define('MODEL_PATH', realpath(APP_PATH . '/Model'));

if (!defined('VIEW_PATH'))
    define('VIEW_PATH', realpath(BASE_PATH . '/themes'));

if (!defined('CACHE_PATH'))
    define('CACHE_PATH', realpath(BASE_PATH . '/var/cache'));

if (!defined('LOGS_PATH'))
    define('LOGS_PATH', realpath(BASE_PATH . '/var/logs'));

/**
 * Default Route Controllers
 *
 */
if (!defined('ROUTE_FRONTEND_DEFAULT_CONTROLLER'))
    define('ROUTE_FRONTEND_DEFAULT_CONTROLLER', 'Index');

if (!defined('ROUTE_BACKEND_DEFAULT_CONTROLLER'))
    define('ROUTE_BACKEND_DEFAULT_CONTROLLER', 'Index');

if (!defined('ROUTE_API_DEFAULT_CONTROLLER'))
    define('ROUTE_API_DEFAULT_CONTROLLER', 'Index');

/**
 * Default Route Methods
 */
if (!defined('ROUTE_FRONTEND_DEFAULT_METHOD'))
    define('ROUTE_FRONTEND_DEFAULT_METHOD', 'index');

if (!defined('ROUTE_BACKEND_DEFAULT_METHOD'))
    define('ROUTE_BACKEND_DEFAULT_METHOD', 'index');

if (!defined('ROUTE_API_DEFAULT_METHOD'))
    define('ROUTE_API_DEFAULT_METHOD', 'index');


/**
 * Session key for each area
 */
if (!defined('SESSION_FRONTEND_AUTH'))
    define('SESSION_FRONTEND_AUTH', 'frontend_login');

if (!defined('SESSION_BACKEND_AUTH'))
    define('SESSION_BACKEND_AUTH', 'backend_login');

if (!defined('SESSION_API_AUTH'))
    define('SESSION_API_AUTH', 'api_login');


/**
 * Url options for access area
 */
if (!defined('BACKEND_AREA_NAME'))
    define('BACKEND_AREA_NAME', 'backend');

if (!defined('API_AREA_NAME'))
    define('API_AREA_NAME', 'api');


/**
 * Login page paths
 */
if (!defined('FRONTEND_LOGIN_PATH'))
    define('FRONTEND_LOGIN_PATH', 'account/login');

if (!defined('BACKEND_LOGIN_PATH'))
    define('BACKEND_LOGIN_PATH', BACKEND_AREA_NAME . '/account/login');


