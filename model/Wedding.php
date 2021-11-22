<?php

namespace model;

use core\Model;
use core\App;

/**
 * Class Wedding
 * @package model
 * @property int id
 * @property int first_user
 * @property int sec_user
 * @property int data_tst
 * @property int peer_id
 * @property int points
 */

class Wedding extends Model
{
    public static string $table = 'weddings';
    public int $main_id = 0;

    public static function findByUserId($id, $peer_id)
    {
        $data1 = App::getPBase()
            ->select('id')
            ->from(static::$table)
            ->where("`peer_id` = '$peer_id' and `first_user` = '$id'" )
            ->queryOne('id');
        $data2 = App::getPBase()
            ->select('id')
            ->from(static::$table)
            ->where("`peer_id` = '$peer_id' and `sec_user` = '$id'" )
            ->queryOne('id');
        if ($data1 > 0) {
            $wed = new Wedding($data1);
            $wed->main_id = $id;
            return $wed;
        } if ($data2 > 0) {
        $wed = new Wedding($data2);
        $wed->main_id = $id;
        return $wed;
        }
        return false;
    }

    public function getPartner(): int
    {
        if($this->main_id == $this->first_user)
            return $this->sec_user;
        else
            return $this->first_user;
    }

    public static function weddingAll($peer_id)
    {
        return App::getPBase()
            ->select()
            ->from(static::$table)
            ->where("`peer_id` = '$peer_id'")
            ->query();
    }
}