<?php


namespace model;

use core\Model;

/**
 * Class Hero
 * @package model
 * @property int $id
 * @property string $class
 * @property int $atk
 * @property int $def
 * @property int $exp
 * @property int $stamina
 * @property int $max_stamina
 * @property int $stamina_tst
 * @property string $status
 * @property int $gold
 * @property int $level
 * @property int $gang
 * @property int $national
 * @property int $target
 */

class Hero extends Model
{
    public static string $table = 'hero';

    public static function findById($id)
    {
        $data = parent::findBy('id', $id);
        if(!is_null($data))
        {
            return new Hero($data);
        }
        return false;
    }
}