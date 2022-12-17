<?php

namespace core;

use ReflectionClass;

/**
 * Class Model
 * @property int $id
 */
class Model
{
    public int $id;
    private mixed $data = [];
    protected static string $table;
    private bool $create = false;

    public function __construct($id = 0)
    {
        $id = intval($id);
        if($id == 0)
            $this->create = true;
        $this->id = $id;
        if($id != 0)
            $this->data = App::getPBase()
                ->select()
                ->from(static::$table)
                ->where("`id`='$id'")
                ->queryOne();
    }

    public function isExists(): bool
    {
        $id = App::getPBase()
            ->select()
            ->from(static::$table)
            ->where("`id`='$this->id'")
            ->queryOne('id');
        if($id != null && $id != 0)
            return true;
        else
            return false;
    }

    public function __get($name): string
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
            if($this->id != 0)
            {
                $insertColumns[] = 'id';
                $insertData[] = $this->id;
            }
            foreach ($this->data as $key => $value)
            {
                $insertColumns[] = $key;
                $insertData[] = App::replaceSpecialChars($value);
            }
            $result = App::getPBase()
                ->insert(static::$table)
                ->column($insertColumns)
                ->value($insertData)
                ->run();
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
                    $update = $update->set($key, App::replaceSpecialChars($value));
            }
            return $update->where("`id` = '$this->id'")->run();
        }
    }

    public function delete()
    {
        if($this->id != 0)
        {
            return App::getPBase()->delete(static::$table)->where("`id` = '$this->id'")->run();
        }
        else
            return false;
    }

    protected static function findBy($name, $value)
    {
        $value = App::replaceSpecialChars($value);
        return App::getPBase()->select()->from(static::$table)->where("`$name` = '$value'")->queryOne('id');
    }

    public static function findAll(): array
    {
        return App::getPBase()->select()->from(static::$table)->query();
    }

    public static function getLastId($where = '')
    {
        $query = App::getPBase()->select('id')
            ->from(static::$table);
        if($where != '')
        {
            $query = $query->where($where);
        }
        return $query->orderBy(['id', 'desc'])
            ->queryOne('id');
    }
}