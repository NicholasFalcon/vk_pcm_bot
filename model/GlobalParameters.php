<?php


namespace model;

use core\Model;


/**
 * Class GlobalParameters
 * @package model
 * @property int $id
 * @property string $name
 * @property int $param1
 */

class GlobalParameters extends Model
{
    public static string $table = 'global_params';

    public static function findById($id)
    {
        $data = parent::findBy('id', $id);
        if(!is_null($data))
        {
            return new GlobalParameters($data);
        }
        return false;
    }
}