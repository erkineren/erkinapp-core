<?php


namespace ErkinApp\Template;


use function ErkinApp\Helpers\convertPublicPathToUrl;

class AssetManager
{
    /**
     * @var AssetItem[]
     */
    private $assets = [];

    /**
     * @var Template
     */
    private $template;

    /**
     * AssetManager constructor.
     * @param Template $template
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    public function add(string $path, array $deps = [])
    {
        $name = pathinfo(parse_url($path)['path'])['basename'];
        $path = $this->normalizaPath($path);
        $this->assets[$name] = new AssetItem($name, $path, $deps);
    }

    public function addCommon(string $path, array $deps = [])
    {
        $name = pathinfo(parse_url($path)['path'])['basename'];
        $path = $this->normalizaPath($path, true);
        $this->assets[$name] = new AssetItem($name, $path, $deps);
    }


    public function normalizaPath($path, bool $commonPath = false)
    {
        $url = parse_url($path);
        if (isset($url['host'])) return $path;

        return ($commonPath ? $this->template->getCommonAssetsPath() : $this->template->getThemeAssetsPath()) . '/' . ltrim($path, '/');
    }

    /**
     * @param null $extensionFilter
     * @return AssetItem[]
     */
    public function all($extensionFilter = null)
    {
        if ($extensionFilter) {
            return array_filter($this->assets, function ($assetItem) use ($extensionFilter) {
                return $assetItem->getExtension() == $extensionFilter;
            });
        }
        return $this->assets;
    }

    public function compile($extension)
    {
        $data = '';
        $latestModification = 0;
        foreach ($this->all($extension) as $assetItem) {
            $modification = $assetItem->getModificationTime();
            if ($modification > $latestModification)
                $latestModification = $modification;
            $data .= file_get_contents($assetItem->getPath()) . PHP_EOL . PHP_EOL;
        }
        $this->deleteFiles($extension);
        return $this->writeToFile($data, $extension);
    }

    public function compileJsAndCss()
    {
        return [
            'js' => $this->compileJs(),
            'css' => $this->compileCss(),
        ];
    }

    public function compileJs()
    {
        return $this->compile('js');
    }

    public function compileCss()
    {
        return $this->compile('css');
    }

    public function getFilename($extension)
    {
        return $this->template->getThemeAssetsPath() . '/cache/' . md5(mb_strtolower($this->template->getName() . $this->template->getArea())) . ".$extension";
    }

    protected function writeToFile($data, $extension)
    {
        $filename = $this->getFilename($extension);
        file_put_contents($filename, $data);
        return $filename;
    }

    public function getPublicFiles($extension)
    {
        return glob(PUBLIC_CAHCE_PATH . "/*.$extension");
    }

    protected function deleteFiles(string $extension)
    {
        $file = $this->getFilename($extension);
        if ($file && is_file($file))
            unlink($file);
    }

    public function getPublicUrl($extension)
    {
        $file = $this->getFilename($extension);
        $url = null;
        if ($file && is_file($file)) {
            $url = convertPublicPathToUrl($file) . '?revision=' . filemtime($file);
        }

        return $url;
    }
}