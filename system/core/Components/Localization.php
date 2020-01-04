<?php


namespace ErkinApp\Components;


class Localization extends DotNotationParameters
{
    public function loadLanguage($lang, $reload = false)
    {
        if ($reload || !$this->has($lang))
            $this->set($lang, $this->getLanguageData($lang));
    }

    public function getLanguageData($lang)
    {
        $langFile = LANGUAGE_PATH . '/' . $lang . '.lang.php';
        if (file_exists($langFile)) {
            $lang = (include $langFile);
        }
        return $lang;
    }

    public function getTranslation($key, $lang = null)
    {
        $lang = $lang ?? ErkinApp()->Config()->get('language');
        $this->loadLanguage($lang);
        return $this->get("$lang.$key") ?? $key;
    }

    function getCurrentLangCode()
    {
        switch ($this->getCurrentLanguage()) {
            case 'turkish':
                return 'tr';
            case 'english':
                return 'en';
            case 'germany':
                return 'de';
            default:
                return null;
        }
    }

    function getCurrentLanguage()
    {
        return ErkinApp()->Config()->get('language');
    }
}