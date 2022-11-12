<?php

namespace Validation\Properties;

class LengthProperty extends Property implements PropertyInterface
{
    const MIN = 'min';
    const MAX = 'max';

    protected array $properties = [
        LengthProperty::MAX => 0,
        LengthProperty::MIN => 0
    ];
}