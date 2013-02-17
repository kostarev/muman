<?php

Class Controller_user Extends Controller_Base {

    function index() {

        if (!isset($this->args[0])) {
            $this->error('Не верная ссылка');
        }

        $id = (int) $this->args[0];
        $Ank = new User($id);
        
        if (!$info = $Ank->get_info()) {
            $this->error('Пользователь не найден в базе данных.');
        }
        
        $this->des->set('user', $info);
        $this->des->set('title', 'Анкета - ' . $info['memb_name']);
        $this->des->display('user');
    }

}

?>