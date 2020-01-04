<?php


namespace ErkinApp;

use Envms\FluentPDO\Query;
use ErkinApp\Components\Config;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Trait AppContainer
 * @package ErkinApp
 * @property-read  Request $request
 * @property-read EventDispatcher $dispatcher
 * @property-read SessionInterface $sessions
 * @property-read ParameterBag $cookies
 * @property-read Model[] $models
 * @property-read string $area
 * @property-read Query $db
 * @property-read Container $container
 * @property-read Logger $logger
 * @property-read Config $config
 */
trait AppContainer
{
    public function __get($name)
    {
        return ErkinApp()->Get($name);
    }
}