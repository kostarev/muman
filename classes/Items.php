<?php
Class Items extends CMS_System{

       //Одиночка паттерн------
    static protected $instance = null;
    //Метод предоставляет доступ к объекту
    static public function me(){
        if (is_null(self::$instance))
            self::$instance = new Items();
        return self::$instance;
    }
    
    protected function __construct() {
        parent::__construct();
    }
    //------------------------
    
    
    
    
    //Преобразование массива предмета в строку
    function item2hex($arr) {
        $arr['id'] = dechex($arr['id']);
        if(strlen($arr['id'])<2){
            $arr['id'] = '0'.$arr['id'];
        }
        $arr['option'] = dechex($arr['option']);
        if(strlen($arr['option'])<2){
            $arr['option'] = '0'.$arr['option'];
        }
        $arr['durability'] = dechex($arr['durability']);
        if(strlen($arr['durability'])<2){
            $arr['durability'] = '0'.$arr['durability'];
        }
        $arr['excellent_str'] = dechex($arr['excellent_str']);
        if(strlen($arr['excellent_str'])<2){
            $arr['excellent_str'] = '0'.$arr['excellent_str'];
        }
         $arr['ancient'] = dechex($arr['ancient']);
        if(strlen($arr['ancient'])<2){
            $arr['ancient'] = '0'.$arr['ancient'];
        }
        
        
        $str = $arr['id']
                . $arr['option']
                . $arr['durability']
                . $arr['serial']
                . $arr['excellent_str']
                . $arr['ancient']
                . $arr['type_str']
                . dechex($arr['harmony']['type'])
                . dechex($arr['harmony']['value'])
                . $arr['sockets'];
        return $str;
    }

    //Получение предмета из строки---
    function hex2item($str) {
        if (strlen($str) <> 32) {
            return false;
        }

        $arr = Array();

        $arr['id'] = hexdec(substr($str, 0, 2));
        if ($arr['id'] == 255) {
            return false;
        }
        $arr['option'] = hexdec(substr($str, 2, 2));
        $arr['is_skill'] = $arr['option'] > 128;
        $arr['level'] = floor(($arr['option'] % 128) / 8);
        $arr['luck'] = $arr['option'] % 4;
        $arr['durability'] = hexdec(substr($str, 4, 2));
        $arr['serial'] = substr($str, 6, 8);
        $excellent = hexdec(substr($str, 14, 2));
        $arr['excellent_str'] = $excellent;
        //Excellent опции
        for ($i = 1; $i <= 6; $i++) {
            $arr['excellent'][$i] = $excellent % 2 ^ $i > 0;
        }

        $arr['ancient'] = hexdec(substr($str, 16, 2));
        $arr['type_str'] = substr($str, 18, 2);
        $arr['type'] = ceil(hexdec($arr['type_str']) / 16);
        $arr['harmony']['type'] = hexdec(substr($str, 20, 1));
        $arr['harmony']['value'] = hexdec(substr($str, 21, 1));
        $arr['sockets'] = substr($str, -10);

        $itemKor = $this->itemKor();
        $arr['KOR'] = $itemKor[$arr['type']][$arr['id']];
        $arr['type_name'] = $this->itemtype($arr['type']);
        $arr['HEX'] = $str;
        return $arr;
    }

    function hextobin($hexstr) {
        if (function_exists('hex2bin')) {
            return hex2bin($hexstr);
        }
        $n = strlen($hexstr);
        $sbin = "";
        $i = 0;
        while ($i < $n) {
            $a = substr($hexstr, $i, 2);
            $c = pack("H*", $a);
            if ($i == 0) {
                $sbin = $c;
            } else {
                $sbin.=$c;
            }
            $i+=2;
        }
        return $sbin;
    }

    //Item(Kor).txt парсер
    function ItemKor() {
        static $items;
        if (isset($items)) {
            return $items;
        }
        $file = D . '/sys/server/Item(Kor).txt';
        if (!is_file($file)) {
            throw new Exception('Поместите файл Item(Kor).txt в папку sys/server');
        }

        $items = Array();
        $arr = file($file);
        $i = 0;
        while (isset($arr[$i])) {
            $str = $arr[$i];
            $i++;
            //Номер категории
            if (is_numeric(trim($str))) {
                $type = (int) trim($str);
                continue;
            }

            //Страшная регулярка, но лучше способ не придумал
            if (preg_match('/([0-9]+)[\s]+([0-9\-]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+"([0-9a-zA-Z\-\)\(\[\]\' ]+)"[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})/', $str, $mas)) {

                $items[$type][$mas[1]] = Array(
                    'id' => $mas[1],
                    'type' => $type,
                    'slot' => $mas[2],
                    'skill' => $mas[3],
                    'x' => $mas[4],
                    'y' => $mas[5],
                    'serial' => $mas[6],
                    'option' => $mas[7],
                    'drop' => $mas[8],
                    'name' => $mas[9],
                    'level' => $mas[10],
                    'mindmg' => $mas[11],
                    'maxdmg' => $mas[12],
                    'attspeed' => $mas[13],
                    'durability' => $mas[14],
                    'magdur' => $mas[15],
                    'magpower' => $mas[16],
                    'lvlreq' => $mas[17],
                    'strreq' => $mas[18],
                    'agireq' => $mas[19],
                    'enereq' => $mas[20],
                    'vitreq' => $mas[21],
                    'cmdreq' => $mas[22],
                    'setattr' => $mas[23],
                    'dwsm' => $mas[24],
                    'dkbk' => $mas[25],
                    'elfme' => $mas[26],
                    'mg' => $mas[27],
                    'dl' => $mas[28],
                    'sum' => $mas[29]
                );
            }
        }
        return $items;
    }

    //Тип предмета в название
    function itemtype($type) {
        $type = (int) $type;
        $arr = $this->item_types();
        if (!isset($arr[$type])) {
            return 'Не найдено';
        }
        return $arr[$type];
    }

    //Массив типов предметов
    function item_types() {
        return Array('Swords', 'Axes', 'Maces & Scepters', 'Spears', 'Bows & Crossbows', 'Staffs', 'Shields', 'Helms', 'Armors', 'Pants', 'Gloves', 'Boots', 'Accessories', 'Miscellaneous I', 'Miscellaneous II', 'Scrolls');
    }


}

?>
