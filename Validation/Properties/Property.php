<?php

namespace Validation\Properties;

use Exception;

abstract class Property implements PropertyInterface
{
    protected array $properties = [];

    public static function create(): Property
    {
        return new static();
    }

    public function set($name, $value): Property
    {
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function get($name)
    {
        if(!isset($this->properties[$name]))
        {
            throw new Exception("undefined property $name");
        }
        return $this->properties[$name];
    }
}