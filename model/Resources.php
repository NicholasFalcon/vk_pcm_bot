<?php


namespace model;

use core\Model;

/**
 * Class Resources
 * @package model
 * @property int $id_resources
 * @property string $name
 * @property int $tier
 */

class Resources extends Model
{
    public static string $table = 'resources';

    public static function findById($id)
    {
        $data = parent::findBy('id_resources', $id);
        if(!is_null($data))
        {
            return new Resources($data);
        }
        return false;
    }
}