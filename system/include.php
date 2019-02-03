<?php

//spl_autoload_register(function ($class_name) {
//    if (file_exists(__DIR__ . '/../libraries/' . $class_name . '.php')) {
//        include_once __DIR__ . '/../libraries/' . $class_name . '.php';
//    } else if (file_exists(__DIR__ . '/libraries/' . $class_name . '.php')) {
//        include_once __DIR__ . '/libraries/' . $class_name . '.php';
//    }
//});

include_once 'config.php';

if (defined('BASE_PATH')) {
    foreach (glob(BASE_PATH . '/helpers/*_helper.php') as $file) {
        include_once $file;
    }
    unset($file);
}

foreach (glob(__DIR__ . '/helpers/*_helper.php') as $file) {
    include_once $file;
}
unset($file);

include_once __DIR__ . '/libraries/simple_html_dom.php';
