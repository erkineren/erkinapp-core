<?php


namespace ErkinApp\Component;


class Localization extends DotNotationParameters
{
    public function getLanguageFile($lang)
    {
        return LANGUAGE_PATH . '/' . $lang . '.lang.php';
    }

    public function hasLanguage($lang)
    {
        return file_exists($this->getLanguageFile($lang));
    }

    public function loadLanguage($lang, $reload = false)
    {
        if ($reload || !$this->has($lang))
            $this->set($lang, $this->getLanguageData($lang));
    }

    public function getLanguageData($lang)
    {
        $data = [];
        if ($this->hasLanguage($lang)) {
            $data = (include $this->getLanguageFile($lang));
        }
        return $data;
    }

    public function getTranslation($key, $lang = null, $default = null)
    {
        $lang = $this->maybeDefaultLanguage($lang ?? $this->getCurrentLanguage());
        return $this->getTranslationFromLanguage($key, $lang) ?? $this->getTranslationFromLanguage($key, $this->getDefaultLanguage()) ?? $default ?? $key;
    }

    public function getTranslationFromLanguage($key, $lang)
    {
        $this->loadLanguage($lang);
        return $this->get("$lang.$key");
    }

    public function getTranslations($lang = null)
    {
        $lang = $lang ?? $this->getCurrentLanguage();
        $this->loadLanguage($lang);
        return $this->get("$lang");
    }

    function getCurrentLanguage()
    {
        return ErkinApp()->Config()->get('language');
    }

    function getDefaultLanguage()
    {
        return ErkinApp()->Config()->get('defaultLanguage');
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

    public function maybeDefaultLanguage($lang)
    {
        return $this->hasLanguage($lang) ? $lang : $this->getDefaultLanguage();
    }

    public function determineLanguage()
    {
        $hrefLang = ErkinApp()->Request()->get('hl');
        $sessionLang = ErkinApp()->Session()->get('hl');
        $lang = $hrefLang ?? $sessionLang ?? $this->getCurrentLanguage();
        $this->setCurrentLanguage($this->maybeDefaultLanguage($lang));
    }
}