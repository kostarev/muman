<?php

Class Controller_item Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);
    }

    function index() {
        if(!isset($this->args[0])){
            $this->error('Не верная ссылка');
        }
        $HEX = Func::filtr($this->args[0]);
        
        try{
            $item = Items::me()->hex2item($HEX);
        }  catch (Exception $e){
            $this->error($e->getMessage());
        }
        
        $this->des->set('item',$item);
        $this->des->set('title', $item['KOR']['name']);
 
        $this->des->display('item');
    }

}

?>
