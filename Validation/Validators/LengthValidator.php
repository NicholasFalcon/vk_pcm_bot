<?php

namespace Validation\Validators;

use Validation\Properties\LengthProperty;

class LengthValidator extends Validator implements ValidatorInterface
{
    public function validate(string $text): bool
    {
        if(mb_strlen($text) < $this->property->get(LengthProperty::MIN))
        {
            $this->error = 'Длина введенных данных меньше '.$this->property->get(LengthProperty::MIN);
            return false;
        }
        if(mb_strlen($text) > $this->property->get(LengthProperty::MAX))
        {
            $this->error = 'Длина введенных данных больше '.$this->property->get(LengthProperty::MAX);
            return false;
        }
        return true;
    }
}