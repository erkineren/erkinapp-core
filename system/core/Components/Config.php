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
        return $this;
    }
}