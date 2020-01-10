<?php


namespace ErkinApp;


use ErkinApp\Exception\ErkinAppException;
use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Class Container
 * @package ErkinApp
 */
class Container extends PimpleContainer implements ContainerInterface
{
    use AppContainer;

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
     * @throws ErkinAppException
     * @throws ReflectionException
     */
    public function maybeBorn($className, $args = [])
    {
        if (!class_exists($className))
            throw new ErkinAppException(sprintf('Can not born. Class "%s" not exist.', $className));
        if (!$this->offsetExists($className)) {
            $this->offsetSet($className, (new ReflectionClass($className))->newInstanceArgs($args));
        }
        return $this->offsetGet($className);
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }

    public function getClassMaps()
    {
        return $this->offsetGet('classMaps');
    }
}