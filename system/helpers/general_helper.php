<?php

namespace ErkinApp\Helpers {

    use Exception;
    use ReflectionClass;
    use ReflectionException;
    use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

    function getClassShortName($class)
    {
        try {
            return (new ReflectionClass($class))->getShortName();
        } catch (ReflectionException $e) {
            return '';
        }
    }

    /**
     * @return string
     */
    function getCurrentControllerPretty()
    {
        $str = (implode(' ', array_map('ucfirst', explode('_', ErkinApp()->getCurrentControllerShortName()))));
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
     * @return FlashBag
     * @throws Exception
     */
    function getFlashBag()
    {
        return ErkinApp()->Session()->getFlashBag();
    }

    /**
     * @param $type
     * @param $message
     * @throws Exception
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

