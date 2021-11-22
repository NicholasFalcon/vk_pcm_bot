<?php


namespace modules;

use PDO;

class PhpBase
{
    private $pdo;
    private $query = '';
    private $lastCommand = '';

    public function __construct($type, $user, $password, $database, $host)
    {
        $dsn = "$type:host=$host;dbname=$database";
        $this->pdo = new PDO($dsn, $user, $password);
    }

    public function exec($q)
    {
        return $this->pdo->exec($q);
    }

    public function getLastError()
    {
        return $this->pdo->errorInfo();
    }

    public function row($q)
    {
        $select = $this->pdo->query($q);
        return $select->fetch(PDO::FETCH_ASSOC);
    }

    public function rows($q)
    {
        $select = $this->pdo->query($q);
//        var_dump($this->pdo->errorInfo());
        return $select->fetchAll(PDO::FETCH_ASSOC);
    }

    public function one($q)
    {
        $select = $this->pdo->query($q);
        return $select->fetch()[0]??null;
    }

    public function update($table)
    {
        $obj = clone $this;
        $obj->query = 'update '.$table;
        $obj->lastCommand = 'update';
        return $obj;
    }

    public function set($setKey, $setValue)
    {
        $obj = clone $this;
        if($obj->lastCommand == 'update')
            $obj->query .= ' set';
        elseif($obj->lastCommand == 'set')
            $obj->query .= ',';
        $obj->query .= " `$setKey`='$setValue'";
        $obj->lastCommand = 'set';
        return $obj;
    }

    public function where($q)
    {
        $obj = clone $this;
        $obj->query .= " where $q";
        $obj->lastCommand = 'where';
        return $obj;
    }

    public function limit($limit)
    {
        $obj = clone $this;
        $obj->query .= " limit $limit";
        $obj->lastCommand = 'limit';
        return $obj;
    }

    public function offset($offset)
    {
        $obj = clone $this;
        $obj->query .= " offset $offset";
        $obj->lastCommand = 'offset';
        return $obj;
    }

    public function groupBy(...$orders)
    {
        $obj = clone $this;
        if($obj->lastCommand == 'where')
            $obj->query .= ' group by';
        elseif($obj->lastCommand == 'groupBy')
            $obj->query .= ',';
        $obj->lastCommand = '';
        if(count($orders) == 1)
            $obj->query .= " {$orders[0]}";
        else
        {
            foreach ($orders as $order)
                $obj = $obj->groupBy($order);
        }
        $obj->lastCommand = 'groupBy';
        return $obj;
    }

    public function orderBy(...$orders)
    {
        $obj = clone $this;
        if($obj->lastCommand == 'where')
            $obj->query .= ' order by';
        elseif($obj->lastCommand == 'groupBy')
            $obj->query .= ' order by';
        elseif($obj->lastCommand == 'orderBy')
            $obj->query .= ',';
        $obj->lastCommand = '';
        if(count($orders) == 1)
            $obj->query .= " {$orders[0][0]} {$orders[0][1]}";
        else
        {
            foreach ($orders as $order)
                $obj = $obj->orderBy($order);
        }
        $obj->lastCommand = 'orderBy';
        return $obj;
    }

    public function run()
    {
        if($this->lastCommand == 'value')
            $this->query .= ')';
        return $this->exec($this->query);
    }

    public function insert($table)
    {
        $obj = clone $this;
        $obj->query = "insert into $table";
        $obj->lastCommand = 'insert';
        return $obj;
    }

    public function column($columns)
    {
        $obj = clone $this;
        if($obj->lastCommand == 'insert')
            $obj->query .= '(';
        elseif($obj->lastCommand == 'column')
            $obj->query .= ',';
        $obj->lastCommand = '';
        $obj->lastCommand = '';
        if(!is_array($columns))
        {
            $obj->query .= "`$columns`";
        }
        else
        {
            foreach ($columns as $column)
            {
                $obj = $obj->column($column);

            }
        }
        $obj->lastCommand = 'column';
        return $obj;
    }

    public function value($values)
    {
        $obj = clone $this;
        if($obj->lastCommand == 'column')
            $obj->query .= ') value (';
        elseif($obj->lastCommand == 'value')
            $obj->query .= ',';
        $obj->lastCommand = '';
        if(!is_array($values))
        {
            $value = addslashes($values);
            $obj->query .= "'$value'";
        }
        else
        {
            foreach ($values as $value)
            {
                $obj = $obj->value($value);

            }
        }
        $obj->lastCommand = 'value';
        return $obj;
    }

    public function onDuplicateKeyUpdate($column, $value)
    {
        $obj = clone $this;
        if($obj->lastCommand == 'value')
            $obj->query .= ') ON DUPLICATE KEY UPDATE';
        elseif($obj->lastCommand == 'onDuplicateKeyUpdate')
            $obj->query .= ',';
        $obj->query .= " `$column` = '$value'";
        $obj->lastCommand = 'onDuplicateKeyUpdate';
        return $obj;
    }

    public function select(...$columns)
    {
        $obj = clone $this;
        if($obj->lastCommand == '' && $obj->query == '')
            $obj->query = 'select';
        elseif($obj->lastCommand == 'select')
            $obj->query .= ',';
        if($columns == [])
            $obj->query .= ' *';
        elseif(count($columns) == 1)
            if(!is_array($columns[0]))
                $obj->query .= " `{$columns[0]}`";
            elseif(count($columns[0]) == 1)
                $obj->query .= " {$columns[0][0]}";
            else
                $obj->query .= " {$columns[0][0]} as {$columns[0][1]}";
        else
        {
            foreach ($columns as $column)
                $obj = $obj->select($column);
        }
        $obj->lastCommand = 'select';
        return $obj;
    }

    public function from($table)
    {
        $obj = clone $this;
        $obj->query .= " from $table";
        $obj->lastCommand = 'from';
        return $obj;
    }

    public function innerJoin($firstTable, $firstKey, $secondTable, $secondKey)
    {
        $obj = clone $this;
        $obj->query .= " inner join $secondTable on $firstTable.`$firstKey`=$secondTable.`$secondKey`";
        $obj->lastCommand = 'innerJoin';
        return $obj;
    }

    public function rightJoin($firstTable, $firstKey, $secondTable, $secondKey)
    {
        $obj = clone $this;
        $obj->query .= " right join $secondTable on $firstTable.`$firstKey`=$secondTable.`$secondKey`";
        $obj->lastCommand = 'rightJoin';
        return $obj;
    }

    public function leftJoin($firstTable, $firstKey, $secondTable, $secondKey)
    {
        $obj = clone $this;
        $obj->query .= " left join $secondTable on $firstTable.`$firstKey`=$secondTable.`$secondKey`";
        $obj->lastCommand = 'leftJoin';
        return $obj;
    }

    public function query()
    {
        return $this->rows($this->query);
    }

    public function queryOne($column = '')
    {
        if(isset($this->rows($this->query)[0]) && $column != '')
            return $this->rows($this->query)[0][$column];
        elseif(isset($this->rows($this->query)[0]))
            return $this->rows($this->query)[0];
        return null;
    }

    public function delete($table)
    {
        $obj = clone $this;
        $obj->query = 'delete from '.$table;
        $obj->lastCommand = 'delete';
        return $obj;
    }

    public function getQuery()
    {
        return $this->query;
    }
}