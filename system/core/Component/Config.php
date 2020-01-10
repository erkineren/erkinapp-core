<?php


namespace ErkinApp\Component;

use ErkinApp\Exception\ErkinAppException;
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
        $this->setPhpSettings();
        $this->loadThemeConfig();
        $this->loadPhinxConfiguration();

        return $this;
    }

    public function setPhpSettings()
    {
        foreach ($this->get('phpsettings') as $varname => $newvalue) {
            ini_set($varname, $newvalue);
        }
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

    public function loadPhinxConfiguration()
    {
        $phinxEnvironments = array_map(function ($values) {
            return [
                'adapter' => 'mysql',
                'host' => $values['host'],
                'name' => $values['dbname'],
                'user' => $values['username'],
                'pass' => $values['password'],
                'port' => $values['port'],
                'charset' => 'utf8',
            ];
        }, $this->get('db'));

        $phinxConfig = $this->getInner('phinx');
        $phinxConfig->mergeRecursive('environments', $phinxEnvironments);

        $this->set('phinx', $phinxConfig->all());
    }
}