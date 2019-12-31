<?php

define('SYS_PATH', __DIR__);
include_once SYS_PATH . '/config/constants.php';

foreach (glob(SYS_PATH . '/helpers/*_helper.php') as $file) {
    include_once $file;
}

if (defined('BASE_PATH')) {
    foreach (glob(BASE_PATH . '/helpers/*_helper.php') as $file) {
        include_once $file;
    }
    unset($file);
}

unset($file);

include_once SYS_PATH . '/libraries/simple_html_dom.php';

$dependencies = require_once SYS_PATH . '/config/dependencies.php';
$events = require_once SYS_PATH . '/config/events.php';
$dependencies();
$events();

if (file_exists(APP_PATH . '/config/dependencies.php')) {
    $dependencies = require_once APP_PATH . '/config/dependencies.php';
    $dependencies();
}
