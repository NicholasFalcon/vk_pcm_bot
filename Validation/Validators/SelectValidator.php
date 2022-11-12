<?php

namespace Validation\Validators;

use Validation\Properties\SelectProperty;

class SelectValidator extends Validator implements ValidatorInterface
{
    public function validate(string $text): bool
    {
        if(!in_array($text, $this->property->get(SelectProperty::SELECT)))
        {
            $this->error = 'Введенные данные не соответствуют значениям ('.implode($this->property->get(SelectProperty::SELECT)).')';
        }
        return true;
    }
}