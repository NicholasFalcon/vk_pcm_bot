<?php

namespace Validation\Validators;

use Validation\Properties\PropertyInterface;

/**
 * @property Validator[] $children
 */
abstract class Validator implements ValidatorInterface
{
    protected string $error = 'Неизвестная ошибка';
    protected ?PropertyInterface $property = null;
    protected array $children = [];

    public static function create(?PropertyInterface $property = null): Validator
    {
        $validator = new static();
        $validator->property = $property;
        return $validator;
    }

    public function validate(string $text): bool
    {
        /**
         * @var Validator $child
         */
        foreach ($this->children as $child)
        {
            if(!$child->validate($text))
            {
                $this->error = $child->error();
                return false;
            }
        }
        return true;
    }

    public function error(): string
    {
        return $this->error;
    }

    public function children(...$validators): Validator
    {
        $this->children = $validators;
        return $this;
    }
}