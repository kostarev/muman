<?php

Class Widgets extends CMS_System{

    private $dir;
    //Одиночка паттерн------
    static protected $instance = null;
    //Метод предоставляет доступ к объекту
    static public function me() {
        if (is_null(self::$instance))
            self::$instance = new Widgets();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct();
        $this->dir = D.'/sys/widgets';
    }
    //------------------------

    
    //Вывод виджета
    function show($name, $params = Array()){
        $file = $this->dir.'/'.$name.'.php';
        if(!is_file($file)){
            return false;
        }
        include $file;
    }
}

?>
