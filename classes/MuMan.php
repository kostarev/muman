<?php

Class MuMan extends CMS_System {

    //Одиночка паттерн------
    static protected $instance = null;
    //Метод предоставляет доступ к объекту
    static public function me(){
        if (is_null(self::$instance))
            self::$instance = new MuMan();
        return self::$instance;
    }
    
    protected function __construct() {
        parent::__construct();
    }
    //------------------------
    

   
    //Изменение денег в банке у юзера
    function bank($user_id, $money) {
        $user_id = (int) $user_id;
         if(!is_numeric($money)){
            throw new Exception('Не верное число для денег');
        }
        $res = $this->db->prepare("UPDATE MEMB_INFO SET mm_money=mm_money+? WHERE memb_guid=?;");
        $res->execute(Array($money, $user_id));
    }


    //Получаем инфу из MEMB_STAT
    function get_memb_stat($id) {
        $res = $this->db->prepare("SELECT * FROM MEMB_STAT WHERE memb___id=?;");
        $res->execute(Array($id));
        return $res->fetch();
    }

}

?>
