<?php

namespace Validation\Validators;

use Validation\Properties\PropertyInterface;

interface ValidatorInterface
{
    public static function create(PropertyInterface  $property = null):Validator;

    public function validate(string $text):bool;

    public function error():string;
}