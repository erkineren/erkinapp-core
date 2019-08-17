<?php

namespace ErkinApp\Helpers {

    use ReflectionClass;

    function get_class_short_name($class)
    {
        return (new ReflectionClass($class))->getShortName();
    }

    /**
     * @return string
     */
    function get_current_controller_pretty()
    {
        $str = (implode(' ', array_map('ucfirst', explode('_', ErkinApp()->getCurrentContollerShortName()))));
        if (strtolower($str) == 'index') return '';
        return $str;
    }

    /**
     * @return string
     */
    function get_current_method_pretty()
    {
        $str = (implode(' ', array_map('ucfirst', explode('_', ErkinApp()->getCurrentMethod()))));
        if (strtolower($str) == 'index') return '';
        return $str;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBag
     */
    function getFlashBag()
    {
        return ErkinApp()->Session()->getFlashBag();
    }

    /**
     * @param $type
     * @param $message
     */
    function set_alert($type, $message)
    {
        getFlashBag()->add($type, $message);
    }

    /**
     * @return bool
     */
    function is_ajax_request()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
}

