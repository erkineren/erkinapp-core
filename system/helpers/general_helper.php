<?php

namespace ErkinApp\Helpers {

    use ReflectionClass;

    function getClassShortName($class)
    {
        try {
            return (new ReflectionClass($class))->getShortName();
        } catch (\ReflectionException $e) {
            return '';
        }
    }

    /**
     * @return string
     */
    function getCurrentControllerPretty()
    {
        $str = (implode(' ', array_map('ucfirst', explode('_', ErkinApp()->getCurrentContollerShortName()))));
        if (strtolower($str) == 'index') return '';
        return $str;
    }

    /**
     * @return string
     */
    function getCurrentMethodPretty()
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
    function setAlert($type, $message)
    {
        getFlashBag()->add($type, $message);
    }

    /**
     * @return bool
     */
    function isAjaxRequest()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
}

