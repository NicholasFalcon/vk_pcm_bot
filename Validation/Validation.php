<?php

namespace Validation;

use Exception;
use Validation\Validators\Validator;

/**
 * Класс валидации
 * @property Validator[] $validators
 */
class Validation
{
    protected array $validators = [];
    const FULL = 'full';

    public static function create(bool $full = false): Validation
    {
        return new static();
    }

    public function getFull($name): bool
    {
        if(!isset($this->validators[$name]))
        {
            return false;
        }
        return in_array(Validation::FULL, $this->validators[$name]);
    }

    public function setValidation($var_name, ...$property): Validation
    {
        $this->validators[$var_name] = $property;
        return $this;
    }

    public function validate($var_name, $value)
    {
        if(isset($this->validators[$var_name]))
        {
            foreach ($this->validators[$var_name] as $validator)
            {
                if($validator instanceof Validator)
                {
                    if(!$validator->validate($value))
                    {
                        return "Ошибка $var_name: ".$validator->error();
                    }
                }
            }
        }
        return true;
    }
}