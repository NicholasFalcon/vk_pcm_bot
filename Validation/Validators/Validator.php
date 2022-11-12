<?php

namespace Validation\Validators;

use Validation\Properties\PropertyInterface;

abstract class Validator implements ValidatorInterface
{
    protected string $error = 'Неизвестная ошибка';
    protected ?PropertyInterface $property = null;

    public static function create(?PropertyInterface $property = null): Validator
    {
        $validator = new static();
        $validator->property = $property;
        return $validator;
    }

    public function validate(string $text): bool
    {
        return true;
    }

    public function error(): string
    {
        return $this->error;
    }
}