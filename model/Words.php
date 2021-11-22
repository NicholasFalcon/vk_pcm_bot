<?php
namespace model;

use core\Model;
use core\App;

/**
 * Class Peer
 * @package model
 * @property string $id
 * @property int $word
 * @property  string $topic
 */
class Words extends Model {

    static string $table = 'words';

    public static function FindRandWord()
    {
        $count = App::getPBase()
            ->select(['count(*)', 'count'])
            ->from(static::$table)
            ->queryOne('count');
        $number = rand(1, $count);
        return App::getPBase()
            ->select('word')
            ->from(static::$table)
            ->limit(1)
            ->offset($number)
            ->queryOne('word');
    }

    public static function findTopic($need_word)
    {
        return App::getPBase()
            ->select('topic')
            ->from(static::$table)
            ->where("`word` = '$need_word'")
            ->queryOne('topic');
    }
}