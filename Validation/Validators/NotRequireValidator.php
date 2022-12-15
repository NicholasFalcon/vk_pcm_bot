<?php

namespace Validation\Validators;

class NotRequireValidator extends Validator implements ValidatorInterface
{
    public function validate(string $text): bool
    {
        if($text == '')
        {
            return true;
        }
        return parent::validate($text);
    }
}