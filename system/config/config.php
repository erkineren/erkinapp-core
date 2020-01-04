<?php

use ErkinApp\Template\Php\PhpTemplate;

$customConfig = include BASE_PATH . '/config/config.php';

return array_replace_recursive([
    'db' =>
        [
            'dsn' => '',
            'host' => 'localhost',
            'port' => '3306',
            'username' => '',
            'password' => '',
            'dbname' => '',
        ],
    'phpsettings' => [
        'error_reporting' => E_ALL & ~E_USER_DEPRECATED,
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
    'language' => 'turkish',
], $customConfig);