<?php

namespace core;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class ComboModel
{
    public $id;
    private $data = [];
    protected static string $table;
    private bool $create = false;
    protected $where;

    public function __construct($ids = [])
    {
        if($ids == [])
            $this->create = true;
        $this->id = $ids;
        if($ids != [])
        {
            $where = '';
            foreach ($ids as $key => $id)
            {
                $where .= "`$key` = '$id' and ";
            }
            $this->where = substr($where, 0, -5);
            $this->data = App::getPBase()->select()->from(static::$table)->where($this->where)->queryOne();
        }
    }

    public function __get($name)
    {
        if(isset($this->data[$name]))
        {
            return $this->data[$name];
        }
        $rp = new ReflectionClass(static::class);
        if (preg_match("~\* @property (?<type>[a-z]*) $name~", $rp->getDocComment(), $matches)) {
            if($matches['type'] == 'int')
            {
                return 0;
            }
        }
        return '';
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function save()
    {
        if($this->create)
        {
            $insertColumns = [];
            $insertData = [];
            foreach ($this->data as $key => $value)
            {
                $insertColumns[] = $key;
                $insertData[] = $value;
            }
            $result = App::getPBase()->insert(static::$table)->column($insertColumns)->value($insertData)->run();
            if($result !== false)
            {
                $this->create = false;
            }
            return $result;
        }
        else
        {
            $update = App::getPBase()->update(static::$table);
            foreach ($this->data as $key => $value)
            {
                if($key != 'id')
                    $update = $update->set($key, $value);
            }
            return $update->where($this->where)->run();
        }
    }

    public function delete()
    {
        if($this->id != 0)
        {
            return App::getPBase()->delete(static::$table)->where($this->where)->run();
        }
        else
            return false;
    }

    protected static function findsBy($columns)
    {
        $where = '';
        foreach ($columns as $column => $value)
        {
            $value = addslashes($value);
            $where .= "`$column` = '$value' and ";
        }
        $where = substr($where, 0, -5);
        return App::getPBase()->select()->from(static::$table)->where($where)->queryOne();
    }

    protected static function findAllBy($columns)
    {
        $where = '';
        foreach ($columns as $column => $value)
        {
            $value = addslashes($value);
            $where .= "`$column` = '$value' and ";
        }
        $where = substr($where, 0, -5);
        return App::getPBase()->select()->from(static::$table)->where($where)->query();
    }

//    public static function findAll($limit = false, $offset = false)
//    {
//        return App::getPBase()->select()->from(static::$table)->query();
//    }

    public function isExists(): bool
    {
        $where = '';
        $id = 'id';
        foreach ($this->id as $key => $value)
        {
            $where .= "`$key` = '$value' and";
            $id = $key;
        }
        $where .= ' 1=1';
        $id = App::getPBase()->select()->from(static::$table)->where($where)->queryOne($id);
        if($id != null && $id != 0)
            return true;
        else
            return false;
    }
}