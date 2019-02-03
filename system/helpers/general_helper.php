<?php
/**
 * Created by PhpStorm.
 * User: erkin
 * Date: 14.12.2018
 * Time: 20:14
 */

if (!function_exists('get_class_short_name')) {
    function get_class_short_name($class)
    {
        return (new \ReflectionClass($class))->getShortName();
    }
}

if (!function_exists('get_current_controller_pretty')) {
    function get_current_controller_pretty()
    {
        $str = (implode(' ', array_map('ucfirst', explode('_', ErkinApp()->getCurrentContollerShortName()))));
        if (strtolower($str) == 'index') return '';
        return $str;
    }
}

if (!function_exists('get_current_method_pretty')) {
    function get_current_method_pretty()
    {
        $str = (implode(' ', array_map('ucfirst', explode('_', ErkinApp()->getCurrentMethod()))));
        if (strtolower($str) == 'index') return '';
        return $str;
    }
}

if (!function_exists('getFlashBag')) {
    /**
     * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBag
     */
    function getFlashBag()
    {
        return ErkinApp()->Session()->getFlashBag();
    }
}

if (!function_exists('set_alert')) {
    function set_alert($type, $message)
    {
        getFlashBag()->add($type, $message);
    }
}

