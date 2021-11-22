<?php


namespace model;

use core\Model;
use core\App;

/**
 * Class Game
 * @package model
 * @property string title
 * @property int user_id
 * @property int peer_id
 * @property int user_send_id
 * @property int reward_tst
 * @property int type_reward
 */

class Rewards extends Model
{
    static string $table = 'rewards';

    public static function findReward($peer_id, $user_id)
    {
        return App::getPBase()
            ->select()
            ->from(static::$table)
            ->where("`user_id` = '$user_id' and `peer_id` = '$peer_id'")
            ->query();
    }

}