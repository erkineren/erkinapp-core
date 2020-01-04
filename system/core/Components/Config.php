<?php


namespace ErkinApp\Components;

use ErkinApp\Exceptions\ErkinAppException;

class Config extends DotNotationParameters
{
    /**
     * @return $this
     * @throws ErkinAppException
     */
    public function load()
    {
        $configFilePath = SYS_PATH . '/config/config.php';
        if (!file_exists($configFilePath))
            throw new ErkinAppException("Config file not found");
        $configData = include $configFilePath;
        $this->setArray($configData);
        $this->loadThemeConfig();
        return $this;
    }

    public function loadThemeConfig()
    {
        $themeConfigFile = realpath(VIEW_PATH . '/' . $this->get('theme.name') . '/' . ErkinApp()->getCurrentArea() . '/theme.config.php');
        if (!isset($themeConfigFile))
            throw new ErkinAppException("Theme config file is not found at $themeConfigFile !");
        $themeConfig = include $themeConfigFile;
        $this->set('theme.config', $themeConfig);
    }
}