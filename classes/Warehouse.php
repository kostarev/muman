<?php

Class Warehouse extends CMS_System{

    
    //Одиночка паттерн------
    static protected $instance = null;
    //Метод предоставляет доступ к объекту
    static public function me(){
        if (is_null(self::$instance))
            self::$instance = new Warehouse();
        return self::$instance;
    }
    
    protected function __construct() {
        parent::__construct();
    }
    //------------------------
    
    //Получение сундука
    function get($id) {
        $arr = Array();
        $res = $this->db->prepare("SELECT * FROM warehouse WHERE AccountID=?;");
        $res->execute(Array($id));
        if (!$row = $res->fetch()) {
            $this->make($id);
            return $arr;
        }
        $arr = $row;
        return $arr;
    }

    //Получение денег из сундука
    function get_money($id) {
        $money = 0;
        $res = $this->db->prepare("SELECT Money FROM warehouse WHERE AccountID=?;");
        $res->execute(Array($id));
        if (!$row = $res->fetch()) {
            $this->make($id);
            return 0;
        }

        return $row['Money'];
    }

    //Получение предметов из сундука
    function get_items($id) {
        static $items_arr;
        if (isset($items_arr[$id])) {
            return $items_arr[$id];
        }
        $res = $this->db->prepare("SELECT Items FROM warehouse WHERE AccountID=?;");
        $res->execute(Array($id));
        if (!$row = $res->fetch()) {
            $this->make($id);
            return Array();
        }

        $str = $row['Items'];

        //Обрезаем 0x
        if (substr($str, 0, 2) == '0x') {
            $str = substr($str, 2);
        }

    
        $items = Array();
        $pos = 0;
        $i = 0;
        //Координаты клетки
        $x = 0;
        $y = 0;

        while ($i < 120 AND $item_str = substr($str, $pos, ITEM_HEX_LEN)) {
            $i++;
            $pos += ITEM_HEX_LEN;
            if (substr_count($item_str, "\0")) {
                $item_str = str_replace("\0", '', $item_str) . '0';
            }

            try{
                $item = Items::me()->hex2item($item_str);
                $item['x'] = $x;
                $item['y'] = $y;
                $items[$item['serial']] = $item;
            }catch(Exception $e){
                
            }
            
            
            $x++;
            if ($x >= 8) {
                $x = 0;
                $y++;
                $pos++;
            }
        }
        $items_arr[$id] = $items;
        return $items;
    }
    
    //Добавление предмета в сундук
    function add_item($id, $HEX){
        //Проверяем нет ли в сундуке предмета с тем же serial
        $items = $this->get_items($id);

        if (isset($items[$item_id])) {
            
            //Меняем serial перемещаемого предмета
            $serial = $item_id;
            while (isset($items[$serial])) {
                $serial = Func::rand_string(8, '0123456789ABCDEF');
            }
            //Формируем новый HEX предмета с учётом нового serial
            $web_items[$item_id]['serial'] = $serial;
            $web_items[$item_id]['HEX'] = Items::me()->item2hex($web_items[$item_id]);
        }


        //Ищем свободное место для предмета
        $pole = Array();
        for ($j = 0; $j < 15; $j++) {
            for ($i = 0; $i < 8; $i++) {
                $pole[$i][$j] = 0;
            }
        }

        $items_kor = Array();
        foreach ($items AS $item) {
            $x = $item['x'];
            $y = $item['y'];
            $w = $item['KOR']['x'];
            $h = $item['KOR']['y'];
            $items_kor[$x][$y]=$item['HEX'];
            for ($j = $y; $j < $y + $h; $j++) {
                for ($i = $x; $i < $x + $w; $i++) {
                    $pole[$i][$j] = 1;
                }
            }
        }

        //габариты предмета
        $w = $web_items[$item_id]['KOR']['x'];
        $h = $web_items[$item_id]['KOR']['y'];
        $newX = -1;
        $newY = -1;
        for ($j = 0; $j < 15; $j++) {
            for ($i = 0; $i < 8; $i++) {
                //Если поле занято, смотрим следующее
                if ($pole[$i][$j]) {
                    continue;
                }
                //Если свободно, смотрим соседние клетки, влезет ли предмет
                else {
                    $ok = true;
                    for ($y = $j; $y < $j + $h; $y++) {
                        for ($x = $i; $x < $i + $w; $x++) {
                            if ($x>7 OR $y>14 OR $pole[$x][$y]) {
                                $ok = false;
                            }
                        }
                    }
                    //Если не влазит, смотрим следующее поле
                    if (!$ok) {
                        continue;
                    }
                    //Иначе, сохраняем новые координаты
                    else {
                        $newX = $i;
                        $newY = $j;
                        break(2);
                    }
                }
            }
        }

        if ($newX == -1 OR $newY == -1) {
            throw new Exception('В сундуке нет места для этого предмета');
        }
        
        //Формируем новый HEX
        $newHEX = '';
        for ($j = 0; $j < 15; $j++) {
            for ($i = 0; $i < 8; $i++) {
                if(!empty($items_kor[$i][$j])){
                 $newHEX.=$items_kor[$i][$j];
                }elseif($i==$newX AND $j==$newY){
                    $newHEX.=$HEX;
                }else{
                    $newHEX.='FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
                }
            }
        }
         
        //Сохраняем новый HEX
        $bin = Items::me()->hextobin($newHEX);
        $res = $this->db->prepare("UPDATE warehouse SET [Items]=? WHERE [AccountID]=?;");
        $res->execute(Array($bin, $id));
    }
    
    //Удаление предмета из сундука
    function del_item($user_id,$serial){
        //Получаем предметы юзера
        $items = $this->get_items($user_id);
        if(!isset($items[$serial])){
            throw new Exception('Нет предмета в сундуке');
        }
        
        //Формируем новый HEX
        $HEX = '';
        foreach($items AS $key => $item){
            if($key==$serial){
                $HEX.='FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
            }
            else{
                $HEX.=$item['HEX'];
            }
        }
        
        //Сохраняем новый HEX
        $bin = Items::me()->hextobin($HEX);
        $res = $this->db->prepare("UPDATE warehouse SET [Items]=? WHERE [AccountID]=?;");
        $res->execute(Array($bin, $user_id));
    }
    
    //Перемещение предметов в сундуке по карте
    function replace_items($id, $item_id,$newX,$newY) {
        $items_pos = Array();
        $newX = (int)$newX;
        $newY=(int)$newY;

        $items = $this->get_items($id);
        if(!isset($items[$item_id])){
            throw new Exception('Такого предмета нет в сундуке');
        }
        
        $width =  $items[$item_id]['KOR']['x'];
        $height = $items[$item_id]['KOR']['y'];
        
        if($newX<0 OR $newX+$width>8 OR $newY<0 OR $newY+$height>15){
            throw new Exception('Заданы не верные координаты');
        }
       
        //Ищем свободное место для предмета
        $pole = Array();
        for ($j = 0; $j < 15; $j++) {
            for ($i = 0; $i < 8; $i++) {
                $pole[$i][$j] = 0;
            }
        }

        $items_kor = Array();
        foreach ($items AS $key=>$item) {
            if($key == $item_id){
                continue;
            }
            $x = $item['x'];
            $y = $item['y'];
            $w = $item['KOR']['x'];
            $h = $item['KOR']['y'];
            $items_kor[$x][$y]=$item['HEX'];
            for ($j = $y; $j < $y + $h; $j++) {
                for ($i = $x; $i < $x + $w; $i++) {
                    $pole[$i][$j] = $key;
                }
            }
        }
        
        //Проверяем возможность перемещения
        $accessible = true;
        for ($j = $newY; $j < $newY+$height; $j++) {
            for ($i = $newX; $i < $newX+$width; $i++) {
                if($pole[$i][$j]){
                    $accessible = false;
                    $error_item = $items[$pole[$i][$j]]['KOR']['name'];
                }
            }
        }
        
        if (!$accessible) {
            throw new Exception('Ячейка занята другим предметом (' . $error_item . ')');
        }
        //----------------------
        //Перемещение возможно
        //
        //Формируем новый HEX
        $HEX = '';
        for ($j = 0; $j < 15; $j++) {
            for ($i = 0; $i < 8; $i++) {
                if(!empty($items_kor[$i][$j])){
                 $HEX.=$items_kor[$i][$j];
                }elseif($i==$newX AND $j==$newY){
                    $HEX.=$items[$item_id]['HEX'];
                }else{
                    $HEX.='FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
                }
            }
        }

        //Сохраняем новый HEX
        $bin = Items::me()->hextobin($HEX);
        $res = $this->db->prepare("UPDATE warehouse SET [Items]=? WHERE [AccountID]=?;");
        $res->execute(Array($bin, $id));
    }

    
    //Изменение денег в сундуке
    function money($user_id, $money) {
        $money = (int) $money;
        $this->make($user_id);
        $money_was = $this->get_money($user_id);
        if($money_was+$money >2000000000){
            throw new Exception('В сундук нельзя поместить больше 2 000 000 000 Zen');
        }
        $res = $this->db->prepare("UPDATE warehouse SET Money=Money+? WHERE AccountID=?;");
        $res->execute(Array($money, $user_id));
    }
    
    //Создание сундука
    function make($id){
        $res = $this->db->prepare("SELECT AccountID FROM warehouse WHERE AccountID=?;");
        $res->execute(Array($id));
        if(!$res->fetch()){
        $res = $this->db->prepare("INSERT INTO warehouse (AccountID) VALUES (?);");
        $res->execute(Array($id));
        return true;
        }
        return false;
    }

}

?>
