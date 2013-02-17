<?php

Class Template extends CMS_System {

    protected $vars = array();
    protected $theme;                 //Тема оформления    
    public $auto_head = true;         //Флаг автоматического вывода head.php и foot.php
    protected $first_display = true;  //Флаг первого вывода
    protected $head = '';             //html код, вставляемы в head
    protected $head_arr = Array();   //Массив html кодов, вставляемых в <head>
    public $menu;

    public function __construct() {
        parent::__construct();
        $this->set_theme('default');
        $this->menu = new Menu();
        if(isset($_GET['ajax'])){
            $this->auto_head = false;
            if($_GET['ajax']=='script'){
            $this->ajax = true;
            }
        }
        elseif(isset($_POST['ajax'])){
            $this->auto_head = false;
            if($_POST['ajax']=='script'){
            $this->ajax = true;
            }
        }
    }

    function set($varname, $value) {
        $this->vars[$varname] = $value;
        return true;
    }

    function remove($varname) {
        unset($this->vars[$varname]);
        return true;
    }

    //Смена темы оформления сайта
    function set_theme($theme) {
        if (!is_dir(D . '/des/' . $theme)) {
            $this->error('Тема оформления' . $theme . 'не найдена');
            return false;
        }
        $this->theme = $theme;
        $this->set('theme', $this->theme);
        return true;
    }

    //Вывод шаблона
    function display($tpl_file) {
        $this->set('menu', $this->menu);
        $this->head = implode("\n", $this->head_arr) . "\n";
        if ($this->auto_head AND $this->first_display) {
            $this->first_display = false;
            $this->display('_head');
        }

        $tpl_file .= '.tpl';
        //Файл шаблона
        $file = $this->theme . '/' . $tpl_file;
        //Если шаблона нет, смотрим в папке default---
        if (!is_file(D . '/des/' . $file)) {
            if (!is_file(D . '/des/default/' . $tpl_file)) {
                $this->error('Шаблон <b>' . $file . '</b> не найден');
            }
            $file = 'default/' . $tpl_file;
        }
        //---------------------------------------------
        //Делаем переменные доступными из шаблона
        extract($this->vars);

        if ($this->user['mm_group'] == 'root' AND $this->conf['developer']['tpl_borders']) {
            echo "<!--start: $file -->\n";
        }

        //Отключаем Ошибки разбора при компиляции шаблонов
        $error_rep_value = error_reporting();
        error_reporting(E_PARSE);
        include D . '/des/' . $file;
        //Включаем отображение ошибок по умолчанию
        error_reporting($error_rep_value);
        if ($this->user['mm_group'] == 'root' AND $this->conf['developer']['tpl_borders']) {
            echo "\n<!--end: $file -->\n";
        }
    }

    //Вывод javascript кода для ajax ответа
    function ajax($tpl_file) {
        $this->auto_head = false;
        $ajax = true;
        //Файл шаблона
        $file = $this->theme . '/' . $tpl_file;
        //Если шаблона нет, смотрим в папке default---
        if (!is_file(D . '/des/' . $file)) {
            if (!is_file(D . '/des/default/' . $tpl_file)) {
                $this->error('Шаблон <b>' . $file . '</b> не найден');
            }
            $file = 'default/' . $tpl_file;
        }
        //---------------------------------------------
        //Экранируем проблемные символы
        foreach ($this->vars AS $key => $val) {
            if (is_string($val)) {
                $this->vars[$key] = str_replace('\\', '\\\\', $this->vars[$key]);
                $this->vars[$key] = str_replace("'", '\\\'', $this->vars[$key]);
                $this->vars[$key] = str_replace("\n", '', $this->vars[$key]);
            }
        }
        //Делаем переменные доступными из шаблона
        extract($this->vars);
        //Отключаем Ошибки разбора при компиляции шаблонов
        error_reporting(E_PARSE);
        include D . '/des/' . $file;
        exit;
    }

    function addCSS($css_name) {
        $css_file = 'des/' . $this->theme . '/' . $css_name . '.css';
        if (!is_file(D . '/' . $css_file)) {
            if (is_file(D . '/des/default/' . $css_name . '.css')) {
                $css_file = 'des/default/' . $css_name . '.css';
                $this->head_arr[] = '<link rel="stylesheet" href="' . H . '/' . $css_file . '" type="text/css" />';
            }
        } else {
            $this->head_arr[] = '<link rel="stylesheet" href="' . H . '/' . $css_file . '" type="text/css" />';
        }
    }

    function __destruct() {

        if ($this->auto_head) {

            //Кол-во запросов в базу----
            $this->set('sql_count', $this->db->get_query_count());
            $this->set('sql_time', round($this->db->get_exec_time_ms(), 3));
            //--------------------------
            //Конец генерации страницы.-----------------
            $this->set('gentime', round(microtime(true) - $_SESSION['start_gen_time'], 3));
            //-----------------------------------------


            $this->display('_foot');

            if ($this->user['mm_group'] == 'root') {
                //Вывод Контроллера и параметров
                if ($this->conf['developer']['params_table']) {
                    echo '<div style="margin:20px;padding:20px;border:solid 1px #cccccc;">Контроллер: <b>' . $this->registry['controller']['name'] . ' (' . $this->registry['controller']['file'] . ')</b><br />Аргументы: <pre>' . htmlspecialchars(print_r($this->registry['args'], true)) . '</pre></div>';
                }

                //Вывод логов БД
                if ($this->conf['developer']['sql_table']) {
                    echo SiteRead::me()->dbLog2html();
                }

                //Вывод Memcache
                if ($this->conf['developer']['memcache_table']) {
                    echo SiteRead::me()->memCache2html();
                }
            }
        }
        
        $this->lang->replace();
    }

}

?>