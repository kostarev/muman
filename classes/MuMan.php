<?php

Class MuMan extends CMS_System {

    private $version, $version_type, $update_server;
    //Одиночка паттерн------
    static protected $instance = null;

    //Метод предоставляет доступ к объекту
    static public function me() {
        if (is_null(self::$instance))
            self::$instance = new MuMan();
        return self::$instance;
    }

    protected function __construct() {
        parent::__construct();
        $this->version = '1.0.1';
        $this->version_type = 'betta';
        $this->update_server = 'http://muman.ru/update';
    }

    //------------------------
    //Версия CMS----
    function version() {
        return $this->version;
    }

    function version_type() {
        return $this->version_type;
    }

    //---------------
    //Загрузка модуля обновления
    function load_update() {
        $last = $this->last_version();
        if ($last['last_version'] == $this->version) {
            throw new Exception('Версии совпадают');
        } elseif ((real) $last['last_version'] < (real) $this->version) {
            throw new Exception('Ваша версия новее');
        }
        $url = $this->update_server . '/get/' . $this->version();
        $smod_file = Modules::me()->modules_dir . '/' . $this->version() . '-' . $last['last_version'] . '.smod';
        Func::load($url, $smod_file);
    }

    //Возвращает последнюю доступную версию движка
    function last_version() {
        if (!$arr = $this->cache->get('last_version')) {
            $url = $this->update_server . '/last_version';
            $resp = Func::http($url);
            $arr = json_decode($resp, true);
            $this->cache->set('last_version', $arr, 5000);
        }
        return $arr;
    }

    //Изменение денег в банке у юзера
    function bank($user_id, $money) {
        $user_id = (int) $user_id;
        if (!is_numeric($money)) {
            throw new Exception('Не верное число для денег');
        }
        $res = $this->db->prepare("UPDATE MEMB_INFO SET mm_money=mm_money+? WHERE memb_guid=?;");
        $res->execute(Array($money, $user_id));
    }

    //Получаем инфу из MEMB_STAT
    function get_memb_stat($id) {
        static $arr;
        if (isset($arr[$id])) {
            return $arr[$id];
        }
        $res = $this->db->prepare("SELECT * FROM MEMB_STAT WHERE memb___id=?;");
        $res->execute(Array($id));
        $arr[$id] = $res->fetch();
        return $arr[$id];
    }

    //Онлайн ли пользователь
    function is_online($user_id) {
        static $arr;
        if (isset($arr[$user_id])) {
            return $arr[$user_id];
        }
        if ($stat = $this->get_memb_stat($user_id) AND $stat['ConnectStat']) {
            $arr[$user_id] = true;
        } else {
            $arr[$user_id] = false;
        }
        return $arr[$user_id];
    }
    
    //Запрет для онлайн юзеров
    function offline_only($user_id){
        if($this->is_online($user_id)){
            throw new Exception('Не доступно, для пользователей, находящихся в игре');
        }
    }

}

?>
