<?php

namespace model;

use core\Model;
use core\App;

/**
 * Class Clan
 * @package model
 * @property int $id
 * @property string $title
 * @property int $owner_id
 * @property int $max_member
 * @property int $level
 * @property string $clan_pin
 * @property int $glory
 * @property int $need_glory
 * @property int $max_level
 */

class Clan extends Model
{
    public static string $table = 'clan';

    public static function findClan($title)
    {
        $data = App::getPBase()
            ->select('id')
            ->from(static::$table)
            ->where("`title` = '$title'")
            ->queryOne('id');
        if ($data > 0) {
            return new Clan($data);
        }
        return false;
    }

    public static function findClanByMember($id)
    {
        $data = App::getPBase()
            ->select('clan_id')
            ->from('clan_member')
            ->where("`member_id` = '$id'")
            ->queryOne('clan_id');
        if ($data > 0) {
            return new Clan($data);
        }
        return false;
    }

    public static function findClanInfoById($id)
    {
        $data = App::getPBase()
            ->select()
            ->from(static::$table)
            ->where("`id` = '$id'")
            ->query();
        if ($data > 0)
        {
            return new Clan($data);
        }
        return false;
    }

    public static function findAllClan()
    {
        return App::getPBase()
            ->select()
            ->from(static::$table)
            ->where(' 1 = 1')
            ->orderBy(['glory', 'desc'])
            ->limit(5)
            ->query();
    }

    public function findMaxMember($user_text)
    {
        return App::getPBase()
            ->select('max_member')
            ->from(static::$table)
            ->where("`title` = '$user_text'")
            ->queryOne('max_member');
    }

    public static function UpdateTitle($title, $owner_id)
    {
        return App::getPBase()
            ->update(static::$table)
            ->set('title', $title)
            ->where("`owner_id` = '$owner_id'")
            ->run();
    }

    public function findCountMember()
    {
        return App::getPBase()
            ->select(['count(*)', 'count'])
            ->from('clan_member')
            ->where("`clan_id` = '$this->id'")
            ->queryOne('count');
    }

    public function kickUser($user_id)
    {
        return App::getPBase()
            ->delete('clan_member')
            ->where("`member_id` = '$user_id'")
            ->run();
    }

    public function findMembers()
    {
        return App::getPBase()
            ->select()
            ->from('clan_member')
            ->where("`clan_id` = '$this->id'")
            ->query();
    }

    public function addNewMember($user_id)
    {
        return App::getPBase()
            ->insert('clan_member')
            ->column(['clan_id', 'member_id'])
            ->value([$this->id, $user_id])
            ->run();
    }
}