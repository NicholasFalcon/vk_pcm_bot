<?php


namespace model;

use core\Model;
use core\App;

/**
 * Class UserConfirmation
 * @package model
 * @property int $user_id
 * @property int $peer_id
 * @property int $tst
 * @property int $type_id
 * @property string $json_data
 */
class UserConfirmation extends Model
{
    public static string $table = 'user_confirmation';

    private array $types = [
        '0' => [
            'name' => 'webConfirm',
            'title' => 'Подтверждение сетки бесед',
            'data_name' => 'web_id'
        ]
    ];

    public static function findByUserIdAndPeerId($user_id, $peer_id)
    {
        $data = App::getPBase()
            ->select('id')
            ->from(static::$table)
            ->where("`user_id` = '$user_id' and `peer_id` = '$peer_id'")
            ->orderBy(['id', 'desc'])
            ->queryOne('id');
        if(!is_null($data))
        {
            return new UserConfirmation($data);
        }
        return false;
    }

    public function getData()
    {
        $data = App::recoverJson($this->json_data);
        return json_decode($data, true);
    }

    public function setData($data)
    {
        $this->json_data = json_encode($data);
    }

    public function getAction(): string
    {
        return $this->types[$this->type_id]['name'];
    }

    public function getTitle(): string
    {
        return $this->types[$this->type_id]['title'] . ' ' . $this->getData()[$this->types[$this->type_id]['data_name']];
    }

    public static function findAllObjByUser($user_id, $page = 0, $limit = 4) : array
    {
        $data = App::getPBase()
            ->select('id')
            ->from(static::$table)
            ->where("`user_id` = '$user_id'")
            ->limit($limit)
            ->offset($page*$limit)
            ->query();
        $objs = [];
        foreach ($data as $item) {
            $objs[] = new UserConfirmation($item['id']);
        }
        return $objs;
    }

    public static function getCountPagesByUser($user_id, $limit = 4) : int
    {
        return intval((App::getPBase()
            ->select(['count(*)', 'count'])
            ->from(static::$table)
            ->where("`user_id` = '$user_id'")
            ->queryOne('count'))/$limit);
    }
}