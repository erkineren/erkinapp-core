<?php

namespace ErkinApp\Template;


use ErkinApp\Exceptions\ViewFileNotFoundException;
use Exception;

abstract class Template implements ITemplate
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string $filename
     * @param array $data
     * @return self
     */
    public function prepare(string $filename, array $data): ITemplate
    {
        $this->filename = $filename;
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return Template
     */
    public function setFilename(string $filename): Template
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return Template
     */
    public function setData(array $data): Template
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getTemplatePath(): string
    {
        return realpath(VIEW_PATH . '/' . ErkinApp()->Config()->get('theme') . '/' . ErkinApp()->getCurrentArea());
    }

    /**
     * @return string
     * @throws ViewFileNotFoundException
     * @throws Exception
     */
    public function getFileFullPath(): string
    {
        $f = $this->getTemplatePath() . '/' . $this->getFilename() . $this->getFileExtension();

        if (!file_exists($f)) {
            throw new ViewFileNotFoundException();
        }

        return $f;
    }


}