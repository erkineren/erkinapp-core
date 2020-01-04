<?php

namespace ErkinApp\Helpers {

    use Exception;

    /**
     * @param $key
     * @param string $label
     * @return string
     */
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

    /**
     * @return string|null
     * @throws Exception
     */
    function getCurrentLangCode()
    {
        return ErkinApp()->Localization()->getCurrentLangCode();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    function getCurrentLanguage()
    {
        return ErkinApp()->Config()->get('language');
    }
}