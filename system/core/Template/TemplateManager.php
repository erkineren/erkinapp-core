<?php


namespace ErkinApp\Template;


use ErkinApp\Exceptions\ErkinAppException;
use ErkinApp\Template\Php\PhpTemplate;
use Exception;
use ReflectionException;

class TemplateManager
{
    /**
     * @var Template
     */
    private $template;

    /**
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return $this
     * @throws ErkinAppException
     * @throws ReflectionException
     */
    public function loadTemplate()
    {
        $templateClassName = ErkinApp()->Config()->get('theme.config.template');
        if (!$templateClassName)
            throw new ErkinAppException("theme.template is empty");

        if (!class_exists($templateClassName))
            throw new ErkinAppException("Template class ($templateClassName) is not exist");

        $this->template = ErkinApp()->Container()->maybeBorn($templateClassName);
        return $this;
    }

    public function setTheme($themeName)
    {
        ErkinApp()->Config()->set('theme.name', $themeName);
        ErkinApp()->Config()->loadThemeConfig();
        return $this->loadTemplate();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getTemplatePath(): string
    {
        return realpath(VIEW_PATH . '/' . ErkinApp()->Config()->get('theme.name') . '/' . ErkinApp()->getCurrentArea());
    }

    /**
     * @param $filename
     * @param array $data
     * @param bool $includeParts
     * @return string
     */
    public function getCompiled($filename, array $data, bool $includeParts)
    {

        if ($this->template instanceof PhpTemplate) {
            $this->template->setIncludePaths($includeParts);
        }

        return $this->template->getCompiled($filename, $data);
    }

    /**
     * @param $filename
     * @param array $data
     * @param bool $includeParts
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function render($filename, array $data, bool $includeParts)
    {
        return $this->template->renderCompiled($this->getCompiled($filename, $data, $includeParts));
    }
}