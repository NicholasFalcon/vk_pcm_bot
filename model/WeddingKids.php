<?php

namespace model;

use core\Model;
use core\App;

/**
 * Class Wedding
 * @package model
 * @property int user_id
 * @property int mother
 * @property int father
 * @property int peer_id
 * @property int sex_tst
 * @property int age
 */
class WeddingKids extends Model
{
    public static string $table = 'wedding_kids';

    public static function FindKid($kid_id, $peer_id)
    {
        $id = App::getPBase()
            ->select('id')
            ->from(static::$table)
            ->where("`peer_id` = '$peer_id' and `user_id` = '$kid_id'")
            ->queryOne('id');
        if ($id > 0)
            return new WeddingKids($id);
        return false;
    }

    public static function FindKidNew($kid_id, $peer_id)
    {
        $id = App::getPBase()
            ->select('id')
            ->from(static::$table)
            ->where("`peer_id` = '$peer_id' and `user_id` = '$kid_id' and `sex_tst` = 0")
            ->queryOne('id');
        if ($id > 0)
            return new WeddingKids($id);
        return false;
    }

    public static function FindKids($user_id, $peer_id)
    {
        $data = App::getPBase()
            ->select()
            ->from(static::$table)
            ->where("`peer_id` = '$peer_id' and `mother` = '$user_id' or `father` = '$user_id'")
            ->query();
        if ($data > 0) {
            return $data;
        }
        return false;
    }

    public static function FindParent($user_id, $peer_id)
    {
        return App::getPBase()
            ->select()
            ->from(static::$table)
            ->where("`peer_id` = '$peer_id' and `user_id` = '$user_id'")
            ->query();
    }
}