<?php

namespace model;

/**
 * @property $name string
 * @property $title string
 * @property $default_value int
 * @property $type string
 */
class Setting extends \core\Model
{
    public static string $table = 'settings';

    public function findByName($name): Setting|bool
    {
        $id = parent::findBy('name', $name);
        if(!is_null($id))
        {
            return new Setting($id);
        }
        return false;
    }
}