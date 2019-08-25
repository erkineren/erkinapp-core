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

    $container['classmaps'] = function (Container $c) {

        $fnc = function ($ctrlFile) {
            $classname = '/Application' . str_replace(APP_PATH, '', $ctrlFile);
            $classname = substr($classname, 0, strrpos($classname, "."));
            $classname = str_replace('/', '\\', $classname);
            return $classname;
        };
        $controllers = [];
        foreach (glob(APP_PATH . '/Controller' . '/{**/*.php,*.php}', GLOB_BRACE) as $ctrlFile) {
            $classname = $fnc($ctrlFile);
            $controllers[$classname] = $ctrlFile;
        }
        $models = [];
        foreach (glob(APP_PATH . '/Model' . '/{**/*.php,*.php}', GLOB_BRACE) as $ctrlFile) {
            $classname = $fnc($ctrlFile);
            $models[$classname] = $ctrlFile;
        }
        return [
            'controllers' => $controllers,
            'models' => $models
        ];
    };

};