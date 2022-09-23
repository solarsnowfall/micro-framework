<?php

namespace Solar\MicroFramework\Container;

use Closure;
use Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected array $definitions = [];

    /**
     * @var array
     */
    protected array $resolved = [];

    /**
     * @param string $id
     * @return mixed
     */
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new NotFoundException("No record found for '$id'");
        }

        if (!array_key_exists($id, $this->resolved)) {
            $this->resolved[$id] = $this->create($id);
        }

        return $this->resolved[$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions) || array_key_exists($id, $this->resolved);
    }

    /**
     * @param $id
     * @return mixed
     */
    protected function create($id): mixed
    {
        $definition = $this->definitions[$id];

        if ($definition instanceof Closure) {
            return $definition($this);
        }

        if (class_exists($id)) {
            return $this->resolveClassInstance($id);
        }

        return $definition;
    }

    /**
     * @param string $class
     * @return mixed
     */
    protected function resolveClassInstance(string $class): mixed
    {
        try {

            $reflection = new ReflectionClass($class);

            if (!$reflection->isInstantiable()) {
                throw new ContainerException("Class '$class' cannot be instantiated");
            }

            $constructor = $reflection->getConstructor();

            if (!$constructor) {
                return $reflection->newInstance();
            }

            $definition = $this->definitions[$class];

            if (is_callable($definition)) {
                return $reflection->newInstanceArgs($definition());
            }

            $isAssoc = array_keys($definition) !== range(0, count($definition) - 1);
            $args = [];

            foreach ($constructor->getParameters() as $i => $parameter) {
                $idx = $isAssoc ? $parameter->getName() : $i;
                if (isset($definition[$idx])) {
                    $args[] = $definition[$idx];
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $args[] = $parameter->getDefaultValue();
                } else {
                    throw new ContainerException("Invalid definition for class '$class'");
                }
            }

            return $reflection->newInstanceArgs($args);

        } catch (Exception $exception) {

            throw new ContainerException(
                "Class $class not suitable for autowiring",
                null,
                $exception
            );
        }
    }
}