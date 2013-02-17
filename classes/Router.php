<?php

Class Router extends CMS_System {

    protected $path;
    protected $args = array();
    
    //Одиночка паттерн
     static protected $instance = null;
    //Метод предоставляет доступ к объекту
    static public function me(){
        if (is_null(self::$instance))
            self::$instance = new Router();
        return self::$instance;
    }
    
    protected function __construct() {
        parent::__construct();
    }
    //-----------------

    function setPath($path) {

        $path = trim($path).'/';

        if (!is_dir($path)) {
            $this->error('Invalid controller path: `' . $path . '`');
        }
        $this->path = $path;
    }

    private function getController(&$file, &$controller, &$action, &$args) {

        $route = (empty($_GET['route'])) ? 'index' : htmlspecialchars(trim($_GET['route'], '/\\'));

        // Получаем раздельные части
        $parts = explode('/', $route);

        // Находим правильный контроллер
        $cmd_path = $this->path;

        foreach ($parts as $part) {
            $fullpath = $cmd_path . $part;

            // Есть ли папка с таким путём?
            if (is_dir($fullpath)) {
                $cmd_path .= $part . '/';
                array_shift($parts);
                continue;
            }

            // Находим файл
            if (is_file($fullpath . '.php')) {
                $controller = $part;
                array_shift($parts);
                break;
            }
        }


        if (empty($controller)) {
            $controller = 'index';
        };

        // Получаем действие
        $action = array_shift($parts);
        if (empty($action) AND $action!=='0') {
            $action = 'index';
        }
        
        $file = $cmd_path . $controller . '.php';
        $args = $parts;
    }

    function delegate() {

        // Анализируем путь
        $this->getController($file, $controller, $action, $args);
        // Файл доступен?
        if (!is_file($file)) {
            $this->error($file . ' - not found<br /> 404 Not Found');
        }

        // Подключаем файл
        include ($file);
        
        
        $this->registry['controller']['name'] = $controller;
        $this->registry['controller']['file'] = $file;

        // Создаём экземпляр контроллер
        $class = 'Controller_' . $controller;
        $controller = new $class($args);

        // Действие доступно?
        if (!is_callable(array($controller, $action))) { 
            array_unshift($args, $action);
            $args[0] = $action;
            $action = 'index';
            $controller = new $class($args);
            if (!is_callable(array($controller, $action))) {
                $this->error('index - not found<br /> 404 Not Found');
            }
        }


        // Выполняем действие
        $controller->$action();
    }

}

?>