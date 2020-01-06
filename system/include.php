<?php

if (!defined('BASE_PATH')) {
//    die('BASE_PATH constant is not defined !');
    define('BASE_PATH', dirname(dirname(dirname((new \ReflectionClass(\Composer\Autoload\ClassLoader::class))->getFileName()))));
}
define('SYS_PATH', __DIR__);

include_once BASE_PATH . '/config/constants.php';
include_once SYS_PATH . '/config/constants.php';

foreach (glob(SYS_PATH . '/helpers/*_helper.php') as $file) {
    include_once $file;
}

foreach (glob(BASE_PATH . '/helpers/*_helper.php') as $file) {
    include_once $file;
}
unset($file);

include_once SYS_PATH . '/libraries/simple_html_dom.php';

$systemDependencies = include_once SYS_PATH . '/config/dependencies.php';
$systemEvents = include_once SYS_PATH . '/config/events.php';
$systemDependencies();
$systemEvents();

if (file_exists(BASE_PATH . '/config/dependencies.php')) {
    $dependencies = include_once BASE_PATH . '/config/dependencies.php';
    $dependencies();
}



