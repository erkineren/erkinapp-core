<?php


namespace ErkinApp;


use ErkinApp\Components\Config;
use ErkinApp\Components\Localization;
use ErkinApp\Exceptions\ErkinAppException;
use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;

/**
 * Class Container
 * @package ErkinApp
 * @property Config config
 * @property Localization localization
 */
class Container extends PimpleContainer implements ContainerInterface
{
    /**
     * @param $name
     * @return mixed
     * @throws ErkinAppException
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * @inheritDoc
     * @throws ErkinAppException
     */
    public function get($id)
    {
        if (!$this->offsetExists($id)) {
            throw new ErkinAppException(sprintf('Identifier "%s" is not defined in container.', $id));
        }
        return $this->offsetGet($id);
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }
}