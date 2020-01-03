<?php

use ErkinApp\Components\Config;
use ErkinApp\Components\Localization;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

return function () {
    $container = ErkinApp()->Container();

    $container[Config::class] = function (Container $c) {
        return (new Config())->load();
    };

    $container[Localization::class] = function (Container $c) {
        return new Localization();
    };

    $container[EventDispatcher::class] = function (Container $c) {
        return new EventDispatcher();
    };

    $container[Request::class] = function (Container $c) {
        $request = Request::createFromGlobals();
        $request->setSession(new Session());
        return $request;
    };

    $container[Logger::class] = function (Container $c) {
        $log = new Logger('app');
        $log->pushHandler(new StreamHandler(BASE_PATH . '/var/logs/app.log', Logger::WARNING));
        return $log;
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
            $controllers[$classname] = realpath($ctrlFile);
        }
        $models = [];
        foreach (glob(APP_PATH . '/Model' . '/{**/*.php,*.php}', GLOB_BRACE) as $ctrlFile) {
            $classname = $fnc($ctrlFile);
            $models[$classname] = realpath($ctrlFile);
        }
        return [
            'controllers' => $controllers,
            'models' => $models
        ];
    };

};