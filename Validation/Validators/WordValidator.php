<?php

namespace Validation\Validators;

class WordValidator extends Validator implements ValidatorInterface
{
    public function validate(string $text): bool
    {
        if($text == '' || strstr($text, ' '))
        {
            $this->error = 'Введенные данные не соответствуют одному слову';
            return false;
        }
        return parent::validate($text);
    }
}