<?php

namespace core;

use Exception;

/**
 * класс для распределения функционала бота
 */
class Routing
{
    const PEER = 'peer';
    const USER = 'user';
    const C_PEER = 'peer_commands';
    const C_USER = 'user_commands';

    protected static string $current_path = '';
    protected static array $routes = [];
    protected static array $routes_user = [];
    protected static array $commands = [];
    protected static array $commands_user = [];
    protected string $type = 'default';
    protected array $last_error = [];


    public function __construct($type = 'peer')
    {
        $this->type = $type;
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

    protected static function set(&$routes, $name, $class, $action, Validation $validation = null)
    {
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
            $command = strtok($name, ' ');
            $pattern = trim(str_replace($command, '', $name));
            $routes[$command]['patterns'][] = [
                'pattern' => $pattern,
                'class' => $class,
                'action' => $action . 'Action',
                'validation' => $validation ?: new Validation()
            ];
        }
    }

    public static function setForPeer($name, $class, $action, Validation $validation = null)
    {
        static::set(static::$routes, $name, $class, $action, $validation);
    }

    public static function setForUser($name, $class, $action, Validation $validation = null)
    {
        static::set(static::$routes_user, $name, $class, $action, $validation);
    }

    protected static function setCommand(&$commands, $name, $class, $action)
    {
        $command = strtok($name, ' ');
        $pattern = trim(str_replace($command, '', $name));
        $commands[$command]['patterns'][] = [
            'class' => $class,
            'action' => $action . 'Action',
            'pattern' => $pattern
        ];
    }

    public static function setCommandForPeer($name, $class, $action)
    {
        static::setCommand(static::$commands, $name, $class, $action);
    }

    public static function setCommandForUser($name, $class, $action)
    {
        static::setCommand(static::$commands_user, $name, $class, $action);
    }

    public function check($path)
    {
        $id = App::$group_id;
        $path = mb_strtolower(trim(preg_replace("~\[club$id\|.*]~", '', $path)));
        if ($this->type == static::PEER) {
            $routes = static::$routes;
            return $this->checkRoute($path, $routes);
        } elseif ($this->type == static::C_PEER) {
            $commands = static::$commands;
            return $this->checkCommands($path, $commands);
        } elseif ($this->type == static::USER) {
            $routes = static::$routes_user;
            return $this->checkRoute($path, $routes);
        } elseif ($this->type == static::C_USER) {
            $commands = static::$commands_user;
            return $this->checkCommands($path, $commands);
        }
        return ['error' => 'undefined_stack', 'msg' => 'Непонятно чего вызывать!'];
    }

    protected function checkRoute($path, $routes)
    {
        $found = false;
        while (!$found) {
            $group = strtok($path, ' ');
            if (isset($routes[$group])) {
                $routes = $routes[$group];
                $path = trim(str_replace($group, '', $path));
            } else {
                if (isset($routes['patterns'])) {
                    foreach ($routes['patterns'] as $pattern) {
                        $res = $this->runRoute($pattern, $path);
                        if (!isset($res['error'])) {
                            return $res;
                        } else {
                            if(!isset($this->last_error['error']) || $this->last_error['error'] != 'validation' || $this->last_error['error'] == 'validation' && $res['error'] == 'validation')
                            {
                                $this->last_error = $res;
                            }
                        }
                    }
                }
                $found = true;
            }
        }
        if ($this->last_error != []) {
            return $this->last_error;
        }
        return false;
    }

    protected function runRoute($route, $path): array
    {
        /**
         * @var Validation $validation
         */
        $pattern = $route['pattern'];
        $validation = $route['validation'];
        $params = [];
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
        if ($path != '') {
            return ['error' => 'bad_routing', 'msg' => 'Обнаружен текст после команды, где он не ожидается'];
        }
        $route['params'] = $params;
        return $route;
    }

    protected function checkCommands($path, $commands)
    {
        $command = strtok($path, ' ');
        $params = trim(str_replace($command, '', $path));
        if (isset($commands[$command])) {
            if (isset($commands[$command]['patterns'])) {
                foreach ($commands[$command]['patterns'] as $pattern) {
                    $res = $this->runCommands($pattern, $params);
                    if (!isset($res['error'])) {
                        return $res;
                    } else {
                        $this->last_error = $res;
                    }
                }
            }
        }
        if ($this->last_error != []) {
            return $this->last_error;
        }
        return false;
    }

    protected function runCommands($route, $path): array
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