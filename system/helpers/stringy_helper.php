<?php

namespace ErkinApp\Helpers {

    use Stringy\Stringy;

    /**
     * @param $str
     * @return Stringy
     */
    function s($str)
    {
        return Stringy::create($str);
    }
}