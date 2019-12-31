<?php


namespace ErkinApp;


use ErkinApp\Exceptions\ErkinAppException;
use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;


class Container extends PimpleContainer implements ContainerInterface
{
    public function __get($name)
    {
        return $this->get($name);
    }

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
            throw new ErkinAppException(sprintf('Identifier "%s" is not defined.', $id));
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