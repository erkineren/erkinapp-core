<?php


namespace ErkinApp\Component;


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

    public function getTranslation($key, $lang = null, $default = null)
    {
        $lang = $lang ?? ErkinApp()->Config()->get('language');
        $this->loadLanguage($lang);
        return $this->get("$lang.$key") ?? $default ?? $key;
    }

    function getCurrentLangCode()
    {
        return ErkinApp()->Config()->get('language');
    }

    function getCurrentLanguage()
    {
        return ErkinApp()->Config()->get('language');
    }

    public function setCurrentLanguage($lang)
    {
        if (is_string($lang)) {
            ErkinApp()->Config()->set('language', $lang);
            ErkinApp()->Session()->set('hl', $lang);
            ErkinApp()->Request()->setLocale($lang);
        }
        return $this;
    }

    public function determineLanguage()
    {
        $hrefLang = ErkinApp()->Request()->get('hl');
        $sessionLang = ErkinApp()->Session()->get('hl');

        $this->setCurrentLanguage($hrefLang ?? $sessionLang ?? $this->getCurrentLanguage());
    }
}