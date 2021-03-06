<?php

namespace ErkinApp\Template\Php;

use ErkinApp\Exception\ViewFileNotFoundException;
use ErkinApp\Template\Template;
use Exception;

class PhpTemplate extends Template
{
    /**
     * @var bool
     */
    private $includePaths = true;

    /**
     * @param bool $includePaths
     * @return PhpTemplate
     */
    public function setIncludePaths(bool $includePaths): PhpTemplate
    {
        $this->includePaths = $includePaths;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludePaths(): bool
    {
        return $this->includePaths;
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return '.php';
    }

    /**
     * @return string
     * @throws ViewFileNotFoundException
     * @throws Exception
     */
    public function resolve(): string
    {
        $viewFile = $this->getFileFullPath();

        extract($this->getData());
        ob_start();

        if ($this->isIncludePaths() && file_exists($this->getTemplatePath() . '/_includes/head.php'))
            include $this->getTemplatePath() . '/_includes/head.php';

        include $viewFile;

        if ($this->isIncludePaths() && file_exists($this->getTemplatePath() . '/_includes/end.php'))
            include $this->getTemplatePath() . '/_includes/end.php';

        return ob_get_clean();
    }

}