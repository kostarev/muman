<?php

Class Controller_char Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);
    }

    function index() {
         if(!isset($this->args[0])){
            $this->error('Не верная ссылка');
        }
        
        $id = Func::filtr($this->args[0]);

        $char = Chars::me()->get_char($id);

        $this->des->set('char',$char);
        $this->des->set('title', $id);
        $this->des->display('char');
    }

}

?>
