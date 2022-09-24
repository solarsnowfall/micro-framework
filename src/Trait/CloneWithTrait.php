<?php

namespace Solar\Microframework\Trait;

trait CloneWithTrait
{
    /**
     * @param array $properties
     * @return CloneWithTrait
     */
    protected function cloneWith(array $properties = []): self
    {
        $diff = [];

        foreach ($properties as $name => $value) {
            if (property_exists($this, $name) && $this->$name !== $value) {
                $diff[$name] = $value;
            }
        }

        if (!count($diff)) {
            return $this;
        }

        $clone = clone $this;

        foreach ($diff as $name => $value) {
            $clone->$name = $value;
        }

        return $clone;
    }
}