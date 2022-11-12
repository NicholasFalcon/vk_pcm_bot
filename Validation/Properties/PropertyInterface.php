<?php

namespace Validation\Properties;

interface PropertyInterface
{
    public static function create():Property;

    public function set($name, $value):Property;

    public function get($name);
}