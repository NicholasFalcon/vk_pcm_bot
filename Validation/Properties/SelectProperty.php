<?php

namespace Validation\Properties;

class SelectProperty extends Property implements PropertyInterface
{
    const SELECT = 'select';

    protected array $properties = [
        SelectProperty::SELECT => []
    ];
}