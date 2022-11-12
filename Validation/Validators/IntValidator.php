<?php

namespace Validation\Validators;

class IntValidator extends Validator implements ValidatorInterface
{
    public function validate(string $text) : bool
    {
        $number = intval($text);
        if($number == $text)
        {
            return true;
        }
        else
        {
            $this->error = 'Введенное значение не соответствует числовому значению';
            return false;
        }
    }
}