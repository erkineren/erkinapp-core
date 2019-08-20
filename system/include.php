<?php

include_once __DIR__ . 'config/constants.php';

foreach (glob(__DIR__ . '/helpers/*_helper.php') as $file) {
    include_once $file;
}

if (defined('BASE_PATH')) {
    foreach (glob(BASE_PATH . '/helpers/*_helper.php') as $file) {
        include_once $file;
    }
    unset($file);
}

unset($file);

include_once __DIR__ . '/libraries/simple_html_dom.php';

$dependencies = require_once __DIR__ . '/config/dependencies.php';
$dependencies();

if (file_exists(APP_PATH . '/config/dependencies.php')) {
    $dependencies = require_once APP_PATH . '/config/dependencies.php';
    $dependencies();
}
