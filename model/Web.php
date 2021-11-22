<?php


namespace model;

use core\Model;
use core\App;

/**
 * Class Web
 * @package model
 * @property string $name
 * @property int $owner_id
 * @property int count_globans
 */
class Web extends Model
{
    public static string $table = 'webs';

    public static function findById($id)
    {
        $data = parent::findBy('id', $id);
        if(!is_null($data))
        {
            return new Web($data);
        }
        return false;
    }

    public static function webList($owner_id)
    {
        return App::getPBase()
            ->select('id', 'name')
            ->from(static::$table)
            ->where("owner_id = '$owner_id'")
            ->query();
    }

    public static function findByWebId($web_id)
    {
        $data = parent::findBy('id', $web_id);
        if(!is_null($data))
        {
            return new Web($data);
        }
        return false;
    }

    public static function findWebByOwner($owner_id)
    {
        $data = parent::findBy('owner_id', $owner_id);
        if(!is_null($data))
        {
            return new Web($data);
        }
        return false;
    }

    public static function findAllByOwnerId($owner_id)
    {
        return App::getPBase()
            ->select('name', 'id')
            ->from(static::$table)
            ->where("`owner_id` = '$owner_id'")
            ->orderBy(['id', 'asc'])
            ->query();
    }

    public static function getCountByOwnerId($owner_id)
    {
        return App::getPBase()
            ->select(['count(*)', 'count'])
            ->from(static::$table)
            ->where("`owner_id` = '$owner_id'")
            ->queryOne('count');
    }

    public function getSettings()
    {
        return App::getPBase()
            ->select('setting_id', 'value')
            ->from('webs_settings')
            ->where("web_id = '$this->id'")
            ->orderBy(['setting_id', 'asc'])
            ->query();
    }

    public function setSetting($setting_id, $value)
    {
        $settings = json_decode(file_get_contents('config/settings.json'), true);
        if(isset($settings[$setting_id]))
        {
            if($settings[$setting_id]['type'] == 'boolean')
                if($value != '1' and $value != '0')
                    return 'error_type';
            return App::getPBase()
                ->insert('webs_settings')
                ->column(['web_id', 'setting_id', 'value'])
                ->value([$this->id, $setting_id, $value])
                ->onDuplicateKeyUpdate('value', $value)
                ->run();
        }
        return 'error_not_found';
    }

    public function getPeersIds()
    {
        return App::getPBase()
            ->select('id')
            ->from('peers')
            ->where("`web_id` = '$this->id'")
            ->query();
    }

    public function setBan($user_id)
    {
        return App::getPBase()
            ->insert('user_web_info')
            ->column(['web_id', 'user_id', 'have_ban'])
            ->value([$this->id, $user_id, 1])
            ->onDuplicateKeyUpdate('have_ban', 1)
            ->run();
    }

    public function unsetBan($user_id)
    {
        return App::getPBase()
            ->insert('user_web_info')
            ->column(['web_id', 'user_id', 'have_ban'])
            ->value([$this->id, $user_id, 0])
            ->onDuplicateKeyUpdate('have_ban', 0)
            ->run();
    }

    public function haveBan($user_id): bool
    {
        $id = App::getPBase()
            ->select('user_id')
            ->from('user_web_info')
            ->where("`user_id` = '$user_id' and `have_ban` = '1' and `web_id` = '$this->id'")
            ->queryOne('user_id');
        if($id == $user_id)
            return true;
        else
            return false;
    }

    public function getAdmins(): array
    {
        $ids = App::getPBase()
            ->select('user_id')
            ->from('user_web_info')
            ->where("`web_id` = '$this->id' and is_Admin = 1")
            ->query();
        $res = [];
        foreach ($ids as $id)
        {
            $res[] = new User($id['user_id']);
        }
        return $res;
    }

    public function setAdmin(User $user)
    {
        return App::getPBase()
            ->insert('user_web_info')
            ->column(['web_id', 'user_id', 'is_admin'])
            ->value([$this->id, $user->id, 1])
            ->onDuplicateKeyUpdate('is_admin', 1)
            ->run();
    }

    public function unsetAdmin(User $user)
    {
        return App::getPBase()
            ->insert('user_web_info')
            ->column(['web_id', 'user_id', 'is_admin'])
            ->value([$this->id, $user->id, 0])
            ->onDuplicateKeyUpdate('is_admin', 0)
            ->run();
    }

    public function isAdmin(User $user): bool
    {
        $id = App::getPBase()
            ->select('user_id')
            ->from('user_web_info')
            ->where("`user_id` = '$user->id' and `is_admin` = '1' and `web_id` = '$this->id'")
            ->queryOne('user_id');
        if($id == $user->id)
            return true;
        else
            return false;
    }
}