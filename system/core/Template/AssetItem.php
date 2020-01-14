<?php


namespace ErkinApp\Template;


class AssetItem
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $dependencies = [];

    /**
     * AssetItem constructor.
     * @param string $name
     * @param string $path
     * @param array $dependencies
     */
    public function __construct(string $name, string $path, array $dependencies = [])
    {
        $this->name = $name;
        $this->path = $path;
        $this->dependencies = $dependencies;
    }

    public function getExtension()
    {
        return pathinfo(parse_url($this->path)['path'])['extension'];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return AssetItem
     */
    public function setName(string $name): AssetItem
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return AssetItem
     */
    public function setPath(string $path): AssetItem
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param array $dependencies
     * @return AssetItem
     */
    public function setDependencies(array $dependencies): AssetItem
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    public function getModificationTime()
    {
        return isset(parse_url($this->path)['host']) ? 0 : filemtime($this->path);
    }

}