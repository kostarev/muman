<?php

Class SiteWrite extends CMS_System {

    //Одиночка паттерн
    static protected $instance = null;

    //Метод предоставляет доступ к объекту
    static public function me() {
        if (is_null(self::$instance))
            self::$instance = new SiteWrite();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct();
    }

    //-----------------
    //Сохранение настроек----
    function save_conf($mother, $name, $value) {
        $res = $this->db->prepare("UPDATE mm_config SET value=? WHERE mother=? AND name=?;");
        $res->execute(Array($value, $mother, $name));
        $this->cache->flush();
    }

    //-----------------------
    //
    //Сохранение настроек доступа------
    function save_access($group, $action, $value) {
        $res = $this->db->prepare("SELECT id FROM mm_actions WHERE name=?;");
        $res->execute(Array($action));
        if (!$action_data = $res->fetch()) {
            throw new Exception("Action $action not found in DataBase");
        }

        $res = $this->db->prepare("SELECT actions FROM mm_groups WHERE name=?;");
        $res->execute(Array($group));
        if (!$row = $res->fetch()) {
            throw new Exception("Group $group not found in DataBase");
        }
        $act_arr = explode(',', $row['actions']);
        if (array_search($action_data['id'], $act_arr) === false) {
            if ($value) {
                $act_arr[] = $action_data['id'];
            }
        } else {
            if (!$value) {
                $id = array_search($action_data['id'], $act_arr);
                unset($act_arr[$id]);
            }
        }


        foreach ($act_arr AS $key => $val) {
            if (!$val) {
                unset($act_arr[$key]);
            }
        }


        $act_str = implode(',', $act_arr);
        $res = $this->db->prepare("UPDATE mm_groups SET actions=? WHERE name=?;");
        $res->execute(Array($act_str, $group));

        $this->cache->flush();
    }

    //---------------------------------
    //Регистрация юзера------
    function registration($arr) {
        $login = !empty($arr['login']) ? $arr['login'] : false;
        $memb_name = !empty($arr['memb_name']) ? $arr['memb_name'] : false;
        $pas = !empty($arr['pas']) ? $arr['pas'] : false;
        $email = !empty($arr['email']) ? $arr['email'] : '';

        //Валидация данных--------
        if ($email AND !Func::valid_email($email)) {
            throw new Exception('Не верный адре электронной почты.');
        }

        if (mb_strlen($login, 'UTF-8') < 3 OR strlen($login) > 10) {
            throw new Exception('Длина логина должна быть от 3х до 10и символов');
        }

        if (mb_strlen($memb_name, 'UTF-8') < 3 OR mb_strlen($memb_name, 'UTF-8') > 10) {
            throw new Exception('Длина имени должна быть от 3х до 10и символов');
        }

        if (mb_strlen($pas, 'UTF-8') < 5 OR mb_strlen($pas, 'UTF-8') > 10) {
            throw new Exception('Длина пароля должна быть 5и до 10и символов.');
        }

        if (!Func::valid_login($login)) {
            throw new Exception('Запрещённые символы в поле Login! Разрешены только буквы латинского алфавита и цифры.');
        }

        if (!Func::valid_name($memb_name)) {
            throw new Exception('Запрещённые символы в поле Имя! Разрешены только буквы латинского алфавита и цифры.');
        }
        //-------------------------

        $res = $this->db->prepare("SELECT memb_guid FROM MEMB_INFO WHERE memb___id=?;");
        $res->execute(Array($login));
        if ($row = $res->fetch()) {
            throw new Exception("Пользователь с логином $login уже зарегистрирован. Выберите другой логин.");
        }

        $res = $this->db->prepare("SELECT memb_guid FROM MEMB_INFO WHERE memb_name=?;");
        $res->execute(Array($memb_name));
        if ($row = $res->fetch()) {
            throw new Exception("Пользователь с именем $memb_name уже зарегистрирован. Выберите другое имя.");
        }

        if ($email) {
            $res = $this->db->prepare("SELECT memb_guid FROM MEMB_INFO WHERE mail_addr=?;");
            $res->execute(Array($email));
            if ($row = $res->fetch()) {
                throw new Exception('Пользователь с таким Email уже зарегистрирован.');
            }
        }

        //Удаляем не подтверждённые аккаунты, старше суток
        $this->db->query("DELETE FROM mm_tmp_users WHERE time<" . TIME . "-3600*24;");

        $res = $this->db->prepare("SELECT login FROM mm_tmp_users WHERE login=?;");
        $res->execute(Array($login));
        if ($row = $res->fetch()) {
            throw new Exception("Пользователь с логином $login ожидает подтверждение регистрации. Выберите другой логин.");
        }

        if ($email) {
            $res = $this->db->prepare("SELECT login FROM mm_tmp_users WHERE email=?;");
            $res->execute(Array($email));
            if ($row = $res->fetch()) {
                throw new Exception('Пользователь с таким Email ожидает подтверждение регистрации.');
            }
        }


        //Шифруем пароль
        if (MD5) {
            $res = $this->db->prepare("SELECT [dbo].[fn_md5](?,?) AS pas;");
            $res->execute(Array($pas, $login));
            $arrs = $res->fetch();
            $md5pas = $arrs['pas'];
        } else {
            $md5pas = $pas;
        }

        //Если требуется подтверждение $email--
        if ($this->conf['reg']['email_must']) {
            if (!$email) {
                throw new Exception('Необходим Email');
            } else {
                $code = Func::rand_string(10);
                $res = $this->db->prepare("INSERT INTO mm_tmp_users (login,memb_name,pas,email,code,time) VALUES (?,?,?,?,?,?);");
                if (!$res->execute(Array($login, $memb_name, $md5pas, $email, $code, TIME))) {
                    throw new Exception('' . $this->db->errorInfo());
                }

                //Высылаем код для подтверждения email
                $from_name = 'Администрация ' . $_SERVER['HTTP_HOST'];
                $from_email = 'admin@' . $_SERVER['HTTP_HOST'];
                $mail_subject = 'Подтверждение регистрации';
                $mail_text = 'Для подтверждения регистрации на сайте ' . H . ' перейдите по следующей ссылке:' . "\n" . H . '/login/email_confirm/' . $code;
                Func::send_mail($from_name, $from_email, $memb_name, $email, $mail_subject, $mail_text);

                return Array('pas' => $md5pas, 'email' => $email, 'login' => $login);
            }
        }

        //-------------------------------------


        $res = $this->db->prepare("INSERT INTO MEMB_INFO (memb___id, memb__pwd, memb_name,mail_addr,mm_reg_time,sno__numb,bloc_code,ctl1_code,mail_chek) VALUES (?,?,?,?,?,'1','0','1','1');");
        if (!$res->execute(Array($login, $md5pas, $memb_name, $email, TIME))) {
            throw new Exception($this->db->errorInfo());
        }

        $arr = $this->db->query("SELECT IDENT_CURRENT('[MEMB_INFO]') AS id;")->fetch();
        $id = $arr['id'];
        return Array('id' => $id, 'pas' => $md5pas, 'email' => $email, 'login' => $login);
    }

    function email_confirm($code) {
        $res = $this->db->prepare("SELECT * FROM mm_tmp_users WHERE code=?;");
        $res->execute(Array($code));
        if (!$row = $res->fetch()) {
            throw new Exception('Не верная ссылка, возможно она устарела. Пройдите регистрацию ещё раз.');
        }

        $res = $this->db->prepare("INSERT INTO MEMB_INFO (memb___id, memb__pwd,memb_name, mail_addr,mm_reg_time,sno__numb,bloc_code,ctl1_code,mail_chek) VALUES (?,?,?,?,?,'1','0','1','1');");
        if (!$res->execute(Array($row['login'], $row['pas'], $row['memb_name'], $row['email'], TIME))) {
            throw new Exception($this->db->errorInfo());
        }

        $arr = $this->db->query("SELECT IDENT_CURRENT('[MEMB_INFO]') AS id;")->fetch();
        $id = $arr['id'];
        $res = $this->db->prepare("DELETE FROM mm_tmp_users WHERE code=?;");
        $res->execute(Array($code));

        return Array('id' => $id, 'pas' => $row['pas'], 'email' => $row['email'], 'login' => $row['login']);
    }

    //Смена группы пользователя
    function change_user_group($user, $group) {

        $Ank = new User($user);
        if (!$info = $Ank->get_info()) {
            throw new Exception('Пользователь не найден');
        }

        if ($info['mm_group'] == 'root') {
            throw new Exception('Права Супер админа изменить нельзя.');
        }
        if ($group == 'root') {
            throw new Exception('Права Супер админа дать нельзя');
        }
        if (!isset($this->groups[$group])) {
            throw new Exception('Такой группы не существует.');
        }

        $res = $this->db->prepare("UPDATE MEMB_INFO SET mm_group=? WHERE memb_guid=?;");
        $res->execute(Array($group, $user));
        return true;
    }

    //-----------------------
    //Рекурсивное удаление папки
    function dirDel($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        if ($objs = glob($dir . "/*")) {
            foreach ($objs AS $obj) {
                is_dir($obj) ? $this->dirDel($obj) : unlink($obj);
            }
        }
        rmdir($dir);
    }

    //--------------------------

    //Добавление действия
    function action_add($name, $title) {
        if ($name AND $title) {
            $res = $this->db->prepare("SELECT name FROM mm_actions WHERE name=?;");
            $res->execute(Array($name));
            if (!$res->fetch()) {
                $res = $this->db->prepare("INSERT INTO mm_actions (name, title) VALUES (?, ?);");
                $res->execute(Array($name, $title));
                $this->cache->flush();
            }
        }
    }
    
    //Удаление действия
    function action_del($name){
        $res = $this->db->prepare("DELETE FROM mm_actions WHERE name=?;");
        $res->execute(Array($name));
    }

}

?>