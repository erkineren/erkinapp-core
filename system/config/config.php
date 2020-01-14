<?php

use ErkinApp\Template\Php\PhpTemplate;

$customConfig = include BASE_PATH . '/config/config.php';

return array_replace_recursive([
    'db' => [
        'default' => [
            'dsn' => '',
            'adapter' => 'mysql',
            'host' => 'localhost',
            'port' => '3306',
            'username' => '',
            'password' => '',
            'dbname' => '',
        ]
    ],
    'phpsettings' => [
        'error_reporting' => 0,
        'display_errors' => 0,
        'date.timezone' => 'Europe/Istanbul',
    ],
    'theme' => [
        'name' => 'default',
        'config' => [
            'title' => 'Default Theme',
            'template' => PhpTemplate::class,
        ]
    ],
    'language' => 'tr',
    'defaultLanguage' => 'tr',
], $customConfig);