<?php

namespace ErkinApp\Template;


use ErkinApp\Event\Events;
use ErkinApp\Event\NotFoundEvent;
use ErkinApp\Exception\ViewFileNotFoundException;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use function ErkinApp\Helpers\convertPublicPathToUrlPath;

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
     * @var AssetManager
     */
    private $assetManager;

    public function __construct()
    {
        $this->assetManager = new AssetManager($this);
    }

    public function getName(): string
    {
        return ErkinApp()->Config()->get('theme.name');
    }

    public function getArea(): string
    {
        return mb_strtolower(ErkinApp()->getCurrentArea());
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
        return realpath(VIEW_PATH . '/' . $this->getName() . '/' . $this->getArea());
    }

    /**
     * @return string
     */
    public function getThemeAssetsPath(): string
    {
        return realpath(PUBLIC_PATH . '/web/assets/themes/' . $this->getName() . '/' . $this->getArea());
    }

    /**
     * @return string
     */
    public function getThemeAssetsUrlPath(): string
    {
        return convertPublicPathToUrlPath($this->getThemeAssetsPath());
    }

    /**
     * @return string
     */
    public function getCommonAssetsPath(): string
    {
        return realpath(PUBLIC_PATH . '/web/assets/common');
    }

    /**
     * @return string
     */
    public function getCommonAssetsUrlPath(): string
    {
        return convertPublicPathToUrlPath($this->getCommonAssetsPath());
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
            /** @var NotFoundEvent $notFoundEvent */
            $notFoundEvent = ErkinApp()->Dispatcher()->dispatch(new NotFoundEvent("Template file not found at $f"), Events::NOT_FOUND);
            if ($notFoundEvent->hasResponse()) {
                $notFoundEvent->getResponse()->send();
                die;
            }

            throw new ViewFileNotFoundException("View file not found at $f");
        }

        return $f;
    }

    public function getAssetManager(): AssetManager
    {
        return $this->assetManager;
    }

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
     * @param $filename
     * @param array $data
     * @return string
     */
    public function getCompiled($filename, $data = [])
    {
        return $this->prepare($filename, $data)->resolve();
    }

    /**
     * @param $filename
     * @param array $data
     * @return Response
     * @throws Exception
     */
    public function render($filename, $data = [])
    {
        return $this->renderCompiled($this->getCompiled($filename, $data));
    }

    /**
     * @param string $compiledView
     * @return Response
     * @throws Exception
     */
    public function renderCompiled(string $compiledView)
    {
        return new Response($compiledView);
    }

}