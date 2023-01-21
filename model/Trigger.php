<?php


namespace model;

use core\Model;
use core\App;

/**
 * Class Trigger
 * @package model
 * @property string $text_trigger
 * @property int $peer_id
 * @property string $command
 * @property string $attach
 */
class Trigger extends Model
{
    public static string $table = 'triggers';

    public static function findByCommand($command, $peer_id)
    {
        $data =  App::getPBase()->select()->from(static::$table)->where("`command` = '$command' and `peer_id` = $peer_id")->queryOne('id');
        if(!is_null($data))
            return new Trigger($data);
        $data2 = App::getPBase()->select()->from(static::$table)->where("`command` = '$command' and `peer_id` = 0")->queryOne('id');
        if (!is_null($data2))
            return new Trigger($data2);
        if($peer_id == 0)
        {
            $data3 = App::getPBase()->select()->from(static::$table)->where("`command` = '$command'")->queryOne('id');
        }
        if (isset($data3))
            return new Trigger($data3);
        return false;
    }

    public static function findByCommandAndPeer($command, $peer_id)
    {
        $data =  App::getPBase()->select()->from(static::$table)->where("`command` = '$command' and `peer_id` = $peer_id")->queryOne('id');
        if(!is_null($data))
            return new Trigger($data);
        return false;
    }

    public static function findByGlobal($command)
    {
        $data =  App::getPBase()->select()->from(static::$table)->where("`command` = '$command' and `peer_id` = 0")->queryOne('id');
        if(!is_null($data))
            return new Trigger($data);
        return false;
    }

    public static function findAllTriggers($peer_id): array
    {
        return App::getPBase()
            ->select('command')
            ->from(static::$table)
            ->where("`peer_id` = '$peer_id'")
            ->query();
    }
}