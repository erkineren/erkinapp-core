<?php


namespace ErkinApp\Template\Smarty;


use ErkinApp\Template\Template;
use Exception;
use Smarty;
use SmartyException;

class SmartyTemplate extends Template
{
    /**
     * @var Smarty
     */
    protected $smarty;

    /**
     * SmartyTemplate constructor.
     */
    public function __construct()
    {
        $this->smarty = new Smarty();
        $this->configure();
    }

    /**
     * @throws Exception
     */
    public function configure()
    {
        $path = CACHE_PATH . '/smarty';
        $this->smarty->setTemplateDir($this->getTemplatePath());
        $this->smarty->setCompileDir("$path/templates_c");
        $this->smarty->setConfigDir("$path/configs");
        $this->smarty->setCacheDir("$path/cache");
        $this->smarty->setPluginsDir([__DIR__ . '/Plugins', SMARTY_PLUGINS_DIR]);

        $this->smarty->setCaching(false);
        $this->smarty->setCompileCheck(true);
    }

    /**
     * @return string
     * @throws SmartyException
     */
    public function resolve(): string
    {
        foreach ($this->getData() as $key => $value) {
            $this->smarty->assign($key, $value);
        }

//        $template = file_get_contents($this->getFileFullPath());
//        return $smarty->fetch('string:' . $template);
        return $this->smarty->fetch($this->getFilename() . $this->getFileExtension());
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return '.tpl';
    }
}