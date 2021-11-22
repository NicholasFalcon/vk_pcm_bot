<?php

namespace model;

use core\Model;
use core\App;

/**
 * Class ClanMember
 * @package Model
 * @property int $clan_id
 * @property int $member_id
 */

class ClanMember extends Model
{
    public static string $table = 'clan_member';
    public int $main_id = 0;

    public static function findAllMember($title)
    {
        return App::getPBase()
            ->select()
            ->from(static::$table)
            ->innerJoin('clan_member','clan_id', 'clan', 'id')
            ->where("`title` = '$title'")
            ->query();
    }

    public static function findMemberByClan($member_id, $clan_id)
    {
        $data = App::getPBase()
            ->select('clan_id')
            ->from(static::$table)
            ->where("`member_id` = '$member_id' and `clan_id` = '$clan_id'")
            ->queryOne('clan_id');
        if ($data > 0)
        {
            $member = new ClanMember($data);
            $member->main_id = $member_id;
            return $member;
        }
        return false;
    }

    public static function findCountMemberByClan($id)
    {
        return App::getPBase()
            ->select(['count(*)', 'count'])
            ->from(static::$table)
            ->where("`clan_id` = '$id'")
            ->queryOne('count');
    }
}