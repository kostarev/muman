<?php

Class Modules extends CMS_System{

    public $modules_dir, $back_modules_dir, $all_files;
   
  //Одиночка паттерн------
    static protected $instance = null;
    //Метод предоставляет доступ к объекту
    static public function me(){
        if (is_null(self::$instance))
            self::$instance = new Modules();
        return self::$instance;
    }
    
    protected function __construct() {
        parent::__construct();
         $this->modules_dir = D . '/sys/modules';
        if (!is_dir($this->modules_dir)) {
            mkdir($this->modules_dir);
        }

        $this->back_modules_dir = D . '/sys/back_modules';
        if (!is_dir($this->back_modules_dir)) {
            mkdir($this->back_modules_dir);
        }
    }
    //------------------------
    
    
    //Список модулей
    function get_modules(){
        
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
        return $modules;
    }
    
    //Проверка существования файла модуля
    function is_mod($fname){
        return is_file($this->modules_dir . '/' . $fname);
    }
    
    //Проверка установленности модуля
    function is_installed($fname){
        $res = $this->db->prepare("SELECT id FROM mm_modules WHERE fname =?;");
        $res->execute(Array($fname));
        return $res->fetch();
    }
    
    //Установка модуля
    function install($fname){
         if (!$this->is_mod($fname)) {
            throw new Exception('Файл модуля не найден');
        }

        $zip = new ZipArchive();
        if ($zip->open($this->modules_dir . '/' . $fname) !== true) {
            throw new Exception('Ошибка открытия файла модуля');
        }

        if ($this->is_installed($fname)) {
            throw new Exception('Модуль ' . $fname . ' уже установлен.');
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
            return true;
        }else{
            $this->all_files = $all_files;
            return false;
        }
    }
    
    
    //Uninstall модуля
    function uninstall($fname){
         if (!$this->is_mod($fname)) {
             throw new Exception('Файл модуля не найден');
        }

        $zip = new ZipArchive();
        if ($zip->open($this->modules_dir . '/' . $fname) !== true) {
            throw new Exception('Ошибка открытия файла модуля');
        }

        $res = $this->db->prepare("SELECT id FROM mm_modules WHERE fname =?;");
        $res->execute(Array($fname));
        if (!$row = $res->fetch()) {
            throw new Exception('Модуль ' . $fname . ' не установлен.');
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
            //$this->des->set('uninstall_str', $uninstall_str);
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
    }
    
    //Удаление файла модуля
    function del($fname){
        if (!$this->is_mod($fname)) {
            throw new Exception('Файл модуля не найден');
        }

        $res = $this->db->prepare("SELECT id FROM mm_modules WHERE fname =?;");
        $res->execute(Array($fname));
        if ($row = $res->fetch()) {
            throw new Exception('Модуль ' . $fname . ' включен. Отключите его перед удалением.');
        }

        unlink($this->modules_dir . '/' . $fname);
    }

}

?>
