<?php

class CMS_System {

    protected $registry;

    protected function __construct() {
        global $registry;
        $this->registry = &$registry;
        //Делаем переменные регистра доступными по ссылке
        foreach ($registry AS $key => $val) {
            $this->$key = &$registry[$key];
        }
    }

    //Вывод ошибки, завершение работы скрипта
    function error($error, $exit = true) {
        $this->registry['des']->set('exit', $exit);
        $this->registry['des']->set('error', $error);
        if ((isset($_POST['ajax']) AND $_POST['ajax']=='script') OR (isset($_GET['ajax']) AND $_GET['ajax']=='script')) {
            $this->registry['des']->ajax('_error.tpl');
        } else {
            $this->registry['des']->display('_error');
        }
        if ($exit) {
            exit;
        }
    }

    //--------------------------------------
}

?>