<?php


namespace ErkinApp\Components;

use ErkinApp\Exceptions\ErkinAppException;
use function ErkinApp\Helpers\isCommandLineInterface;

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
        $this->setPhpSettings();
        return $this;
    }

    public function loadThemeConfig()
    {
        $themeConfigFile = realpath(VIEW_PATH . '/' . $this->get('theme.name') . '/' . ErkinApp()->getCurrentArea() . '/theme.config.php');
        if ($themeConfigFile) {
            $themeConfig = include $themeConfigFile;
            $this->set('theme.config', $themeConfig);
        } else {
            if (!isCommandLineInterface())
                throw new ErkinAppException("Theme config file is not found at $themeConfigFile !");
        }
    }

    public function setPhpSettings()
    {
        foreach ($this->get('phpsettings') as $varname => $newvalue) {
            ini_set($varname, $newvalue);
        }
    }
}