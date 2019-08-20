<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;

return function () {
    $container = ErkinApp()->Container();

    $container['logger'] = function (Container $c) {
        $log = new Logger('app');

        if (defined('BASE_PATH')) {
            $log->pushHandler(new StreamHandler(BASE_PATH . '/app.log', Logger::WARNING));
        }

    };


};