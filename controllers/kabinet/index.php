<?php

Class Controller_index Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);

        if(!$this->user['id']){
            $this->error('Необходимо авторизироваться.');
        }
    }

    function index() {

        $this->des->set('title', $this->user['memb_name'].' - Кабинет');
        $this->des->display('kabinet/index');
    }

}

?>
