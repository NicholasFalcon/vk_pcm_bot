<?php

namespace Validation\Validators;

class RequireValidator extends Validator implements ValidatorInterface
{
    public function validate(string $text): bool
    {
        if($text == '')
        {
            $this->error = 'Не введены обязательные данные';
            return false;
        }
        return true;
    }
}