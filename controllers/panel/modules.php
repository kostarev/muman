<?php

Class Controller_modules Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);
        
        SiteRead::me()->access('change-modules');
        $this->modules_dir = D . '/sys/modules';
        if (!is_dir($this->modules_dir)) {
            mkdir($this->modules_dir);
        }

        $this->back_modules_dir = D . '/sys/back_modules';
        if (!is_dir($this->back_modules_dir)) {
            mkdir($this->back_modules_dir);
        }
    }

    public function index() {

        $this->des->set('title', 'Панель - Модули');
        $this->des->set('title_html', '<a href="' . H . '/panel">Панель</a> - Модули');
        $this->des->display('panel/modules');
    }

    function install() {

        //Загрузка файла---
        if (isset($_FILES['file'])) {

            if ($_FILES['file']['error']) {
                $this->error('Ошибка загрузки файла');
            }

            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            if ($ext <> 'smod') {
                $this->error('Допускаются только файлы с расширением <b>smod</b>');
            }

            $zip = new ZipArchive();
            if ($zip->open($_FILES['file']['tmp_name']) === true) {
                $zip->close();

                $mod_fname = $this->modules_dir . '/' . $_FILES['file']['name'];
                if (is_file($mod_fname)) {
                    $this->error('Файл с именем ' . $_FILES['file']['name'] . ' уже есть в папке модулей.');
                }
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $mod_fname)) {
                    $this->error('Ошибка перемещения файла в папку модулей');
                }

                $this->loc(H . '/panel/modules/installed');
            } else {
                $this->error('Ошибка открытия. Возможно, загруженный файл не является zip архивом.');
            }
        }
        //-----------------


        $this->des->set('install', true);
        $this->des->set('title', 'Панель - Модули - Загрузка модуля');
        $this->des->set('title_html', '<a href="' . H . '/panel">Панель</a> - <a href="' . H . '/panel/modules">Модули</a> - Загрузка модуля');
        $this->des->display('panel/modules');
    }

    function installed() {

        $res = $this->db->prepare("SELECT id FROM mm_modules WHERE fname=?;");
        $modules = Array();
        $zip = new ZipArchive();
        $modules_arr = scandir($this->modules_dir);
        foreach ($modules_arr AS $key => $mod_file) {
            if ($mod_file == '.' OR $mod_file == '..') {
                continue;
            }

            if ($zip->open($this->modules_dir . '/' . $mod_file) === true) {

                $ini_string = $zip->getFromName('info.ini');
                $zip->close();

                $info_arr = parse_ini_string($ini_string);
                $info_arr['fname'] = $mod_file;

                $res->execute(Array($mod_file));
                if ($row = $res->fetch()) {
                    $info_arr['installed'] = true;
                } else {
                    $info_arr['installed'] = false;
                }

                $modules[$mod_file] = $info_arr;
            }
        }

        $this->des->set('modules', $modules);
        $this->des->set('installed', true);
        $this->des->set('title', 'Панель - Модули - Загруженые');
        $this->des->set('title_html', '<a href="' . H . '/panel">Панель</a> - <a href="' . H . '/panel/modules">Модули</a> - Загруженые');

        $this->des->display('panel/modules');
    }

    function info() {

        if (!isset($this->args[0])) {
            $this->error('Не верная ссылка');
        }
        $fname = $this->args[0];
        if (!is_file($this->modules_dir . '/' . $fname)) {
            $this->error('Файл модуля не найден');
        }

        $zip = new ZipArchive();
        if ($zip->open($this->modules_dir . '/' . $fname) !== true) {
            $this->error('Ошибка открытия файла модуля');
        }

        $res = $this->db->prepare("SELECT id FROM mm_modules WHERE fname =?;");
        $res->execute(Array($fname));
        if ($row = $res->fetch()) {
            $this->error('Модуль ' . $fname . ' уже установлен.');
        }

        $conflict = false;
        $numFiles = $zip->numFiles;
        $all_files = Array();
        $res = $this->db->prepare("SELECT mm_modules_files.id, mm_modules.fname
            FROM mm_modules_files 
            JOIN mm_modules ON mm_modules.id=mm_modules_files.module
            WHERE mm_modules_files.fname=?;");
        for ($i = 0; $i < $numFiles; $i++) {
            $all_files[$i] = $zip->statIndex($i);
            $all_files[$i]['is_dir'] = (strrchr($all_files[$i]['name'], '/') === '/');

            $res->execute(Array($all_files[$i]['name']));
            if ($row = $res->fetch()) {
                $all_files[$i]['conflict'] = $row['fname'];
                $conflict = true;
            } else {
                $all_files[$i]['conflict'] = false;
            }

            if ($all_files[$i]['is_dir'] OR in_array($all_files[$i]['name'], Array('install.php', 'uninstall.php', 'info.ini'))) {
                continue;
            }

            $all_files[$i]['replace'] = is_file(D . '/' . $all_files[$i]['name']);
        }

        //Установка-------
        if (!$conflict) {
            $res = $this->db->prepare("INSERT INTO mm_modules (fname) VALUES (?);");
            $res->execute(Array($fname));
            
            $arr = $this->db->query("SELECT IDENT_CURRENT('[mm_modules]') AS id;")->fetch();
            $module_id = $arr['id'];
            //$module_id = $this->db->lastInsertId();
            $res = $this->db->prepare("INSERT INTO mm_modules_files (module, fname) VALUES (?, ?);");
            $back_zip = new ZipArchive();
            $back_zip_file = $this->back_modules_dir . '/' . $fname . '.zip';
            $back_zip->open($back_zip_file, ZipArchive::CREATE);


            foreach ($all_files AS $key => $val) {
                if ($val['is_dir'] OR in_array($val['name'], Array('install.php', 'uninstall.php', 'info.ini'))) {
                    continue;
                }
                $res->execute(Array($module_id, $val['name']));
                if (is_file(D . '/' . $val['name'])) {
                    $back_zip->addFile($val['name']);
                }
            }
            $back_zip->close();

            $zip->extractTo(D);

            if (is_file(D . '/install.php')) {
                ob_start();
                include D . '/install.php';
                $install_str = ob_get_contents();
                ob_end_clean();
            }


            unlink(D . '/info.ini');
            unlink(D . '/uninstall.php');
            unlink(D . '/install.php');

            /*
              $this->des->set('ok', true);
              $this->des->set('install_str', $install_str);
             */
            $zip->close();
            $this->cache->flush();
            $this->loc(H . '/panel/modules/installed');
        }
        //---------------
       //$zip->close();

        $this->des->set('title', $fname);
        $this->des->set('module', $fname);
        $this->des->set('files', $all_files);
        $this->des->set('info', true);
        $this->des->display('panel/modules');
    }

    function uninstall() {

        if (!isset($this->args[0])) {
            $this->error('Не верная ссылка');
        }
        $fname = $this->args[0];
        if (!is_file($this->modules_dir . '/' . $fname)) {
            $this->error('Файл модуля не найден');
        }

        $zip = new ZipArchive();
        if ($zip->open($this->modules_dir . '/' . $fname) !== true) {
            $this->error('Ошибка открытия файла модуля');
        }

        $res = $this->db->prepare("SELECT id FROM mm_modules WHERE fname =?;");
        $res->execute(Array($fname));
        if (!$row = $res->fetch()) {
            $this->error('Модуль ' . $fname . ' не установлен.');
        }
        $module_id = $row['id'];

        //Выполняем uninstall.php модуля---
        if ($uninstall_php = $zip->getFromName('uninstall.php')) {
            file_put_contents(D . '/uninstall.php', $uninstall_php);
            ob_start();
            include D . '/uninstall.php';
            $uninstall_str = ob_get_contents();
            ob_end_clean();
            unlink(D . '/uninstall.php');
            $this->des->set('uninstall_str', $uninstall_str);
        }
        //----------------------------------
        
        //Удаляем файлы модуля-----
        $res = $this->db->prepare("SELECT fname FROM mm_modules_files WHERE module=?;");
        $res->execute(Array($module_id));
        $rows = $res->fetchAll();
        foreach($rows AS $row){
            unlink(D . '/'.$row['fname']);
        }
        //-------------------------
        
        
        //Удаляем информацию о модуле из базы---
        $res = $this->db->prepare("DELETE FROM mm_modules_files WHERE module=?;");
        $res->execute(Array($module_id));
        $res = $this->db->prepare("DELETE FROM mm_modules WHERE id=?;");
        $res->execute(Array($module_id));
        //------------------------------------
       
        $zip->close();
        
        //Возвращаем забэкапленные файлы если есть
        if ($zip->open($this->back_modules_dir . '/' . $fname . '.zip') === true) {
         $zip->extractTo(D);
         $zip->close();
        }
        
        unlink($this->back_modules_dir . '/' . $fname . '.zip');

        $this->cache->flush();
        $this->loc(H . '/panel/modules/installed');
        /*
          $this->des->set('uninstall', true);
          $this->des->display('panel/modules');
         
         */
         
    }

    function del() {
        if (!isset($this->args[0])) {
            $this->error('Не верная ссылка');
        }
        $fname = $this->args[0];
        if (!is_file($this->modules_dir . '/' . $fname)) {
            $this->error('Файл модуля не найден');
        }

        $res = $this->db->prepare("SELECT id FROM mm_modules WHERE fname =?;");
        $res->execute(Array($fname));
        if ($row = $res->fetch()) {
            $this->error('Модуль ' . $fname . ' включен. Отключите его перед удалением.');
        }

        unlink($this->modules_dir . '/' . $fname);
        $this->loc(H . '/panel/modules/installed');
    }

}

?>
