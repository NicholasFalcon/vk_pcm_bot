<?php

namespace core;

use Exception;
use function PHPUnit\Framework\throwException;

/**
 * Класс валидации
 */
class Validation
{
    const REQUIRE = 'require';
    const INTEGER = 'int';
    const WORD = 'word';
    const CHAR = 'char';

    protected array $validations = [];
    protected static array $selectors = [];
    protected static array $lengths = [];

    public function setValidation($var_name, ...$property): Validation
    {
        $this->validations[$var_name] = $property;
        return $this;
    }

    public static function SELECT($var_name, ...$values): string
    {
        static::$selectors[$var_name] = $values;
        return 'select';
    }

    public static function LENGTH($var_name, $length)
    {
        static::$lengths[$var_name] = $length;
        return 'length';
    }

    public function validate($var_name, $value)
    {
        if(isset($this->validations[$var_name]))
        {
            foreach ($this->validations[$var_name] as $property)
            {
                if($res = call_user_func([$this, 'is_'.$property], $var_name, $value) !== true)
                {
                    return $res;
                }
            }
        }
        return true;
    }

    /**
     * @param $var_name
     * @param $value
     * @return bool|string
     * @throws Exception
     */
    protected function is_select($var_name, $value)
    {
        if(isset(static::$selectors[$var_name]))
        {
            if(in_array($value, static::$selectors[$var_name]))
            {
                return true;
            }
            return "Параметр '$var_name' не соответствует ожидаемым: ".implode(', ', static::$selectors[$var_name]);
        }
        throw new Exception("Неизсветная переменная '$var_name'");
    }

    protected function is_int($var_name, $value)
    {
        if(is_int($value))
        {
            return true;
        }
        return "Параметр '$var_name' не соответствует числовому полю";
    }

    protected function is_require($var_name, $value)
    {
        if($value != '')
        {
            return true;
        }
        return "Параметр '$var_name' обязателен";
    }

    protected function is_word($var_name, $value)
    {
        if($value != '' && !strstr($value, ' '))
        {
            return true;
        }
        return "Параметр '$var_name' не соответствует слову";
    }

    protected function is_char($var_name, $value)
    {
        if(strlen($value) == 1)
        {
            return true;
        }
        return "Параметр '$var_name' не соответствует символу";
    }

    /**
     * @param $var_name
     * @param $value
     * @return bool|string
     * @throws Exception
     */
    protected function is_length($var_name, $value)
    {
        if(isset(static::$lengths[$var_name]))
        {
            if(strlen($value) <= static::$lengths[$var_name])
            {
                return true;
            }
            return "Длина параметра '$var_name' превышена";
        }
        throw new Exception("Неизсветная переменная '$var_name'");
    }
}