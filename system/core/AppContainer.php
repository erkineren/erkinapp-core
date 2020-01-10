<?php


namespace ErkinApp;

use ErkinApp\Component\Config;
use ErkinApp\Component\Localization;
use ErkinApp\Template\TemplateManager;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait AppContainer
 * @package ErkinApp
 * @property-read Config config
 * @property-read Localization localization
 * @property-read EventDispatcher dispatcher
 * @property-read Request request
 * @property-read Logger logger
 * @property-read TemplateManager templateManager
 * @property-read array classMaps
 * @property-read ParameterBag cookies
 * @property-read string area
 */
trait AppContainer
{
    public function __get($name)
    {
        return ErkinApp()->Get($name);
    }
}