<?php

Class Controller_pers Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);

        if(!$this->user['id']){
            $this->error('Необходимо авторизироваться.');
        }
    }

    function index() {
        
        $this->des->set('title', $this->user['memb_name'].' - Мои персонажи');
        //Получаем список персонажей
        try{
        $chars = Chars::me()->get_user_chars($this->user['memb___id']);
        $this->des->set('chars',$chars);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }
 
        $this->des->display('pers_list');
    }

}

?>
