<?php

Class Controller_item Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);

        if(!$this->user['id']){
            $this->error('Необходимо авторизироваться.');
        }
    }

    function index() {
        if(!isset($this->args[0])){
            $this->error('Не верная ссылка');
        }
        $id = Func::filtr($this->args[0]);
        //Получаем список предметов
        $items = Warehouse::me()->get_items($this->user['memb___id']);
        if(!isset($items[$id])){
            $this->error('Предмета нет в сундуке');
        }
        
        $item = $items[$id];
        
        $this->des->set('item',$item);
        $this->des->set('title', $item['KOR']['name']);
 
        $this->des->display('item');
    }

}

?>
