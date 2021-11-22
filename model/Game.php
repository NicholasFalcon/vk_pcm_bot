<?php


namespace model;

use core\Model;
use core\App;

/**
 * Class Game
 * @package model
 * @property int $id
 * @property int $peer_id
 * @property string $title
 * @property int $checker
 * @property string $need_word
 * @property string $wrong
 * @property string $right
 * @property int $numb_shoot
 * @property int $timer
 */

class Game extends Model
{
    static string $table = 'game_by_peer';

    public static function findGame($title, $peer_id)
    {
        $data = App::getPBase()
            ->select('id')
            ->from(static::$table)
            ->where("`title` = '$title' and `peer_id` = '$peer_id'")
            ->queryOne('id');
        if ($data > 0)
        {
            return new Game($data);
        }
        return false;
    }

    public static function updateGameStatus($title, $peer_id, $check)
    {
        return App::getPBase()
            ->update(static::$table)
            ->set('checker', $check)
            ->set('wrong', null)
            ->set('right', null)
            ->where("`title` = '$title' and `peer_id` = '$peer_id'")
            ->run();
    }
}