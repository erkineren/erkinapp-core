<?php

namespace ErkinApp\Helpers {


    function ln($key, $label = '')
    {
        $line = ErkinApp()->Localization()->getTranslation($key);
        if (!$line) return implode(' ', array_map('ucfirst', explode('_', $key)));

        if (is_array($label) && count($label) > 0) {
            $_line = vsprintf($line, $label);
        } else {
            $_line = @sprintf($line, $label);
        }
        return $_line;
    }

    function getCurrentLangCode()
    {
        return ErkinApp()->Localization()->getCurrentLangCode();
    }

    function getCurrentLanguage()
    {
        return ErkinApp()->Config()->get('language');
    }
}