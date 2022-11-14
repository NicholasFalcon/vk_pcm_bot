<?php

namespace Validation\Validators;

class CharValidator extends Validator implements ValidatorInterface
{
    public function validate(string $text): bool
    {
        if(strlen($text) != 1)
        {
            $this->error = 'Введенные данные не соответствуют одному символу';
            return false;
        }
        return parent::validate($text);
    }
}