<?php

namespace ErkinApp\Helpers {
    function loadDefaultLanguage()
    {
        if (!defined('DEFAULT_LANGUAGE')) return [];

        return loadLanguage(DEFAULT_LANGUAGE);
    }

    function loadLanguage($lang)
    {
        $langFile = LANGUAGE_PATH . '/' . $lang . '.lang.php';
        if (file_exists($langFile)) {
            $lang = (include $langFile);
        }
        return $lang;
    }

    function ln($key, $label = '')
    {
        $line = ErkinApp()->Language($key);
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

        if (defined('DEFAULT_LANGUAGE')) {
            switch (strtolower(DEFAULT_LANGUAGE)) {
                case 'turkish':
                    return 'tr';
                case 'germany':
                    return 'de';
                default:
                    return 'en';
            }
        }

        return 'en';
    }
}