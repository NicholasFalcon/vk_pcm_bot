<?php

namespace core;

use Exception;

/**
 * класс для распределения функционала бота
 */
class Routing
{
    protected static string $current_path = '';
    protected static array $routes = [];
    protected static array $commands = [];
    protected string $type = 'default';
    protected array $last_error = [];


    public function __construct($type = null)
    {
        if (!is_null($type)) {
            $this->type = 'commands';
        }
    }

    /**
     *
     * @param $group_name
     * @param $fn
     */
    public static function group($group_name, $fn)
    {
        $save_path = static::$current_path;

        static::$current_path .= '&' . $group_name;
        static::$current_path = trim(static::$current_path, '&');
        call_user_func($fn);

        static::$current_path = $save_path;
    }

    public static function set($name, $class, $action, Validation $validation = null)
    {
        $routes = &static::$routes;

        if (static::$current_path != '') {
            foreach (explode('&', static::$current_path) as $path) {
                if (!isset($routes[$path])) {
                    $routes[$path] = [];
                }
                $routes = &$routes[$path];
            }
            $routes['patterns'][] = [
                'pattern' => $name,
                'class' => $class,
                'action' => $action . 'Action',
                'validation' => $validation ?: new Validation()
            ];
        } else {
            $routes[$name]['patterns'][] = [
                'pattern' => '',
                'class' => $class,
                'action' => $action . 'Action',
                'validation' => $validation ?: new Validation()
            ];
        }
    }

    public static function setCommands($name, $class, $action)
    {
        $command = strtok($name, ' ');
        $pattern = trim(str_replace($name, '', $command));
        static::$commands[$command]['patterns'][] = [
            'class' => $class,
            'action' => $action . 'Action',
            'pattern' => $pattern
        ];
    }

    public function check($path)
    {
        if ($this->type == 'default') {
            return $this->checkDefault($path);
        } else {
            return $this->checkCommands($path);
        }
    }

    /**
     * @param $path
     * @return array|false
     */
    protected function checkDefault($path)
    {
        $found = false;
        $routes = static::$routes;
        while (!$found) {
            $group = strtok($path, ' ');
            if (isset($routes[$group])) {
                $routes = $routes[$group];
                $path = trim(str_replace($group, '', $path));
            } else {
                if (isset($routes['patterns'])) {
                    foreach ($routes['patterns'] as $pattern) {
                        $res = $this->runDefault($pattern, $path);
                        if (!isset($res['error'])) {
                            return $res;
                        } else {
                            $this->last_error = $res;
                        }
                    }
                }
                $found = true;
            }
        }
        if($this->last_error != [])
        {
            return $this->last_error;
        }
        return false;
    }

    protected function runDefault($route, $path): array
    {
        /**
         * @var Validation $validation
         */
        $pattern = $route['pattern'];
        $validation = $route['validation'];
        $params = [];
        if ($pattern != '') {
            while ($pattern != '') {
                $elem = strtok($pattern, ' ');
                if (strstr($elem, ':')) {
                    $var_name = trim($elem, ':');
                    if ($var_name != 'user_text') {
                        $value = strtok($path, ' ');
                    } else {
                        $value = $path;
                    }
                    if (($error = $validation->validate($var_name, $value)) !== true) {
                        return ['error' => 'validation', 'msg' => $error];
                    }
                    $params[$var_name] = $value;
                } else {
                    $value = strtok($path, ' ');
                    if ($elem != $value) {
                        return ['error' => 'routing', 'msg' => 'Путь не найден'];
                    }
                }
                $pattern = trim(str_replace($elem, '', $pattern));
                $path = trim(str_replace($value, '', $path));
            }
        } elseif ($path != '') {
            return ['error' => 'bad_routing', 'msg' => 'Обнаружен текст после команды, где он не ожидается'];
        }
        $route['params'] = $params;
        return $route;
    }

    protected function checkCommands($path)
    {
        $command = strtok($path, ' ');
        if (isset(static::$commands[$command])) {
            if(isset(static::$commands[$command]['patterns']))
            {
                foreach (static::$commands[$command]['patterns'] as $pattern)
                {
                    $res = $this->runCommands(static::$commands[$command], $path);
                    if (!isset($res['error'])) {
                        return $res;
                    } else {
                        $this->last_error = $res;
                    }
                }
            }
        }
        if($this->last_error != [])
        {
            return $this->last_error;
        }
        return false;
    }

    public function runCommands($route, $path)
    {
        $pattern = $route['pattern'];
        $params = [];
        while ($pattern != '') {
            $elem = strtok($pattern, ' ');
            $var_name = trim($elem, ':');
            if ($var_name != 'user_text') {
                $value = strtok($path, ' ');
            } else {
                $value = $path;
            }
            if ($value == '') {
                return ['error' => 'empty_var', 'msg' => "Переменная '$var_name' не указана"];
            }
            $params[$var_name] = $value;
            $pattern = trim(str_replace($elem, '', $pattern));
            $path = trim(str_replace($value, '', $path));
        }
        $route['params'] = $params;
        return $route;
    }

    /**
     * @param $dir
     * @throws Exception
     */
    public static function installRoute($dir)
    {
        $dir = trim($dir, '/');
        if (!is_dir($dir)) {
            throw new Exception('Директория роутов указана неверно');
        }
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir("$dir/$file")) {
                    static::installRoute("$dir/$file");
                } else {
                    include_once "$dir/$file";
                }
            }
        }
    }
}