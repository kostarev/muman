<?php

Class Items extends CMS_System {

    //Одиночка паттерн------
    static protected $instance = null;

    //Метод предоставляет доступ к объекту
    static public function me() {
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
        if (strlen($arr['id']) < 2) {
            $arr['id'] = '0' . $arr['id'];
        }

        $arr['level_bin'] = decbin($arr['level']);
        $strlen = strlen($arr['level_bin']);
        for ($i = $strlen; $i < 4; $i++) {
            $arr['level_bin'] = '0' . $arr['level_bin'];
        }


        $arr['opt_bin'] = decbin(($arr['option'] - ($arr['option'] > 12) * 16) / 4);
        if (strlen($arr['opt_bin']) < 2) {
            $arr['opt_bin'] = '0' . $arr['opt_bin'];
        }

        $arr['option_bin'] = ($arr['is_skill'] ? '1' : '0')
                . $arr['level_bin']
                . ($arr['luck'] ? '1' : '0')
                . $arr['opt_bin'];

        $arr['option_hex'] = base_convert($arr['option_bin'], 2, 16);
        if (strlen($arr['option_hex']) < 2) {
            $arr['option_hex'] = '0' . $arr['option_hex'];
        }
        $arr['durability_hex'] = dechex($arr['durability']);
        if (strlen($arr['durability_hex']) < 2) {
            $arr['durability_hex'] = '0' . $arr['durability_hex'];
        }

        $exc_str = '';
        for ($i = 1; $i <= 6; $i++) {
            $exc_str.=$arr['excellent'][$i] ? '1' : '0';
        }
        $arr['excellent_bin'] = (($arr['option'] > 12) ? '1' : '0') . $exc_str;
        $arr['excellent_hex'] = base_convert($arr['excellent_bin'], 2, 16);

        if (strlen($arr['excellent_hex']) < 2) {
            $arr['excellent_hex'] = '0' . $arr['excellent_hex'];
        }
        $arr['ancient_hex'] = dechex($arr['ancient']);
        if (strlen($arr['ancient_hex']) < 2) {
            $arr['ancient_hex'] = '0' . $arr['ancient_hex'];
        }


        $str = $arr['id']
                . $arr['option_hex']                     //0,2
                . $arr['durability_hex']                 //3,2
                . $arr['serial']                         //5,8
                . $arr['excellent_hex']                  //13,2
                . $arr['ancient_hex']                    //15,2
                . dechex($arr['type'])                   //17,1
                . dechex($arr['opt108'])                     //19,1
                . dechex($arr['h_type'])                 //20,1
                . dechex($arr['h_val'])                  //21,1
                . $arr['sockets'];                       //22,10
        return $str;
    }

    //Получение предмета из строки---
    function hex2item($str) {
        $str = preg_replace('|([^0-9a-fA-F])|', '', $str);
        if (strlen($str) <> 32) {
            throw new Exception('Не верный HEX предмета');
        }

        $arr = Array();

        //0-1 байт
        $arr['id'] = hexdec(substr($str, 0, 2));
        if ($arr['id'] == 255) {
            throw new Exception('Не верный id предмета');
        }
        //2-4 байт
        $arr['option_hex'] = hexdec(substr($str, 2, 2));
        //[is_skill]{1}  [level]{4}  [?]{1}  [luck]{1}  [?]{1}
        $arr['option_bin'] = base_convert(substr($str, 2, 2), 16, 2) + 100000000;
        $arr['is_skill'] = substr($arr['option_bin'], 1, 1);
        $arr['level'] = bindec(substr($arr['option_bin'], 2, 4));
        //Ограничеваем level
        if ($arr['level'] > 13) {
            $arr['level'] = 13;
        }
        $arr['luck'] = substr($arr['option_bin'], 6, 1);
        $arr['option'] = bindec(substr($arr['option_bin'], 7, 2)) * 4;

        $arr['durability'] = hexdec(substr($str, 4, 2));
        $arr['serial'] = substr($str, 6, 8);

        $arr['excellent_bin'] = base_convert(substr($str, 14, 2), 16, 2) + 10000000;
        $arr['option'] += substr($arr['excellent_bin'], 1, 1) * 16;

        $arr['ancient'] = hexdec(substr($str, 16, 2));
        $arr['type'] = hexdec(substr($str, 18, 1));
        
        $arr['opt108'] = substr($str, 19, 1);
        $arr['h_type'] = hexdec(substr($str, 20, 1));
        $arr['h_val'] = hexdec(substr($str, 21, 1));
        
        $harmonys = $this->harmonys();
        if($arr['h_type'] AND $this->itemType2HarmonyType($arr['type'])){
        $arr['harmonys'] = $harmonys[$this->itemType2HarmonyType($arr['type'])][$arr['h_type']];
        }
        
        $arr['sockets'] = substr($str, -10);

        $arr['option_str'] = $this->options_name($arr['type'], $arr['level']);
        //Excellent опции
        for ($i = 1; $i <= 6; $i++) {
            $arr['excellent'][$i] = substr($arr['excellent_bin'], $i + 1, 1);
            if ($arr['excellent'][$i]) {
                $arr['excellent_str'][] = $this->excellent_option_name($arr['type'], $i - 1, $arr['level']);
            }
        }

        $itemKor = $this->itemKor();
        $arr['KOR'] = $itemKor[$arr['type']][$arr['id']];
        $itemAddOption = $this->itemAddOption();
        $arr['addoption'] = isset($itemAddOption[$arr['type']][$arr['id']]) ? $itemAddOption[$arr['type']][$arr['id']] : Array();
        $skillKor = $this->skillKor();
        $arr['skill'] = isset($skillKor[$arr['KOR']['skill']]) ? $skillKor[$arr['KOR']['skill']] : Array();
        $arr['type_name'] = $this->itemtype($arr['type']);
        $arr['HEX'] = $str;
        return $arr;
    }

    function hextobin($hexstr) {
        if (function_exists('hex2bin')) {
            return hex2bin($hexstr);
        }
        $n = strlen($hexstr);
        $sbin = '';
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
    
    //Типы предметов
    function types(){
        return Array('Swords','Axes','Spears','Bows & Crossbows','Staffs','Shields','Helms','Armors','Pants','Gloves','Boots','Accessories','Miscellaneous I','Miscellaneous II','Scrolls');
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

        //Ключи для разных типов предмета
        $keys = Array();
        $keys[0] = Array('id', 'slot', 'skill', 'x', 'y', 'serial', 'option', 'drop', 'name', 'level', 'mindmg', 'maxdmg', 'attspeed', 'dur', 'magdur', 'magpower', 'lvlreq', 'strreq', 'agireq', 'enereq', 'vitreq', 'cmdreq', 'setattr', 'dw/sm', 'dk/bk', 'elf/me', 'mg', 'dl', 'sum');
        $keys[1] = $keys[0];
        $keys[2] = $keys[0];
        $keys[3] = $keys[0];
        $keys[4] = $keys[0];
        $keys[5] = $keys[0];
        $keys[6] = Array('id', 'slot', 'skill', 'x', 'y', 'serial', 'option', 'drop', 'name', 'level', 'def', 'successblock', 'dur', 'lvlreq', 'strreq', 'agireq', 'enereq', 'vitreq', 'cmdreq', 'setattr', 'dw/sm', 'dk/bk', 'elf/me', 'mg', 'dl', 'sum');
        $keys[7] = Array('id', 'slot', 'skill', 'x', 'y', 'serial', 'option', 'drop', 'name', 'level', 'def', 'magdef', 'dur', 'lvlreq', 'strreq', 'agireq', 'enereq', 'vitreq', 'cmdreq', 'setattr', 'dw/sm', 'dk/bk', 'elf/me', 'mg', 'dl', 'sum');
        $keys[8] = $keys[7];
        $keys[9] = $keys[7];
        $keys[10] = $keys[7];
        $keys[11] = $keys[7];
        $keys[12] = Array('id', 'slot', 'skill', 'x', 'y', 'serial', 'option', 'drop', 'name', 'level', 'def', 'dur', 'lvlreq', 'enereq', 'strreq', 'dexreq', 'comreq', 'buymoney', 'dw/sm', 'dk/bk', 'elf/me', 'mg', 'dl', 'sum');
        $keys[13] = Array('id', 'slot', 'skill', 'x', 'y', 'serial', 'option', 'drop', 'name', 'level', 'dur', 'res1', 'res2', 'res3', 'res4', 'res5', 'res6', 'res7', 'setattr', 'dw/sm', 'dk/bk', 'elf/me', 'mg', 'dl', 'sum');
        $keys[14] = Array('id', 'slot', 'skill', 'x', 'y', 'serial', 'option', 'drop', 'name', 'value', 'level');
        $keys[15] = Array('id', 'slot', 'skill', 'x', 'y', 'serial', 'option', 'drop', 'name', 'level', 'lvlreq', 'enereq', 'buymoney', 'dw/sm', 'dk/bk', 'elf/me', 'mg', 'dl', 'sum');

        foreach ($arr AS $str) {
            //Номер категории
            if (is_numeric(trim($str))) {
                $type = (int) trim($str);
                continue;
            }

            //Страшная регулярка, но лучше способ не придумал
            if (preg_match('/([0-9]+)[\s]+([0-9\-]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+"([0-9a-zA-Z\-\)\(\[\]\' ]+)"[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})[\s]{0,}([0-9]{0,})/', $str, $mas)) {
                unset($mas[0]);

                foreach ($mas AS $k => $v) {
                    if (isset($keys[$type][$k - 1])) {
                        $items[$type][$mas[1]][$keys[$type][$k - 1]] = $v;
                    }
                }
            }
        }
        return $items;
    }

    //ItemAddOption.txt парсер
    function itemAddOption() {
        static $options;
        if (isset($options)) {
            return $options;
        }
        $file = D . '/sys/server/ItemAddOption.txt';
        if (!is_file($file)) {
            throw new Exception('Поместите файл ItemAddOption.txt в папку sys/server');
        }

        $options = Array();
        $arr = file($file);

        foreach ($arr AS $str) {
            //Номер категории
            if (is_numeric(trim($str))) {
                $type = (int) trim($str);
                continue;
            }

            if (preg_match('/([0-9]{1,2})[\s]+([0-9]+)[\s]+([0-9]{1})[\s]+([0-9]+)[\s]+([0-9]{1})[\s]+([0-9]+)[\s]+([0-9]+)/', $str, $mas)) {
                unset($mas[0]);
                $options[$mas[1]][$mas[2]][$type] = Array('opt1' => $mas[3], 'val1' => $mas[4], 'opt2' => $mas[5], 'val2' => $mas[6], 'time' => $mas[7], 'opt1_name' => $this->addoptions_name($type, $mas[3]), 'opt2_name' => $this->addoptions_name($type, $mas[5]), 'option_kat' => $this->addoption_kat($type));
            }
        }

        return $options;
    }

    //Skill(Kor).txt парсер
    function skillKor() {
        static $skills;
        if (isset($skills)) {
            return $skills;
        }
        $file = D . '/sys/server/Skill(Kor).txt';
        if (!is_file($file)) {
            throw new Exception('Поместите файл Skill(Kor).txt в папку sys/server');
        }

        $skills = Array();
        $arr = file($file);
        $skill_types = $this->skill_types();

        foreach ($arr AS $str) {
            if (preg_match('/([0-9]{1,3})[\s]+"([0-9a-zA-Z\-\)\( ]+)"[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)[\s]+([0-9\-]+)/', $str, $mas)) {
                unset($mas[0]);
                $skill['id'] = $mas[1];
                $skill['name'] = $mas[2];

                foreach ($skill_types AS $key => $val) {
                    if (empty($mas[$key + 3])) {
                        continue;
                    }
                    $skill['opt'][$val] = $mas[$key + 3];
                }
                $skills[$mas[1]] = $skill;
            }
        }
        return $skills;
    }

    //JewelOfHarmonyOption.txt парсер
    function harmonys() {
        static $harms;
        if (isset($harms)) {
            return $harms;
        }
        $file = D . '/sys/server/JewelOfHarmonyOption.txt';
        if (!is_file($file)) {
            throw new Exception('Поместите файл JewelOfHarmonyOption.txt в папку sys/server');
        }

        $harms = Array();
        $arr = file($file);
        $harm_keys = Array('id', 'name', 'weight', 'minlvl', 'lvl0', 'zen0', 'lvl1', 'zen1', 'lvl2', 'zen2', 'lvl3', 'zen3', 'lvl4', 'zen4', 'lvl5', 'zen5', 'lvl6', 'zen6', 'lvl7', 'zen7', 'lvl8', 'zen8', 'lv9', 'zen9', 'lvl10', 'zen10', 'lvl11', 'zen11', 'lvl12', 'zen12', 'lvl13', 'zen13');

        foreach ($arr AS $str) {
            //Номер категории
            if (is_numeric(trim($str))) {
                $type = (int) trim($str);
                continue;
            }
            if (preg_match('/([0-9]{1,3})[\s]+"([0-9a-zA-Z\-\)\( ]+)"[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)/', $str, $mas)) {
                unset($mas[0]);
                foreach($harm_keys AS $k => $v){
                $harms[$type][$mas[1]][$v] = $mas[$k+1];
                }
            }
        }
        return $harms;
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

    //Массив опций
    function addoptions_name($type, $id) {
        $arr = Array();
        $arr[0] = Array(0, 'Attack Success Rate', 'Additional Damage', 'Defense Success Rate', 'PVP Add Defense', 'Max HP', 'Max SD', 'SD auto recovery', 'SD Recovery Rate');
        return isset($arr[$type][$id]) ? $arr[$type][$id] : 'Не найдена';
    }

    //Категории опций
    function addoption_kat($id) {
        $arr = Array('PVP');
        return isset($arr[$id]) ? $arr[$id] : 'Не известно';
    }

    //Названия опции в зависимости от типа предмета
    function options_name($type, $level) {
        $arr = Array();
        //Оружие
        $arr[0] = 'Additional dmg +' . $level;
        $arr[1] = $arr[0];
        $arr[2] = $arr[0];
        $arr[3] = $arr[0];
        $arr[4] = $arr[0];
        $arr[5] = $arr[0];
        //Щиты
        $arr[6] = 'Additional defense rate +' . $level;
        //Доспехи
        $arr[7] = 'Additional defense +' . $level;
        $arr[8] = $arr[7];
        $arr[9] = $arr[7];
        $arr[10] = $arr[7];
        $arr[11] = $arr[7];
        //Accessories
        $arr[12] = $arr[0];
        //Miscellaneous
        $arr[13] = 'Automatic HP recovery +' . ($level / 6);
        $arr[14] = $arr[13];
        //Свитки
        $arr[15] = 'Нет опций';
        return $arr[$type];
    }

    //Название excellent опций для типов предмета
    function excellent_option_name($type, $id, $level) {
        $arr = Array();
        //Оружие
        $arr[0] = Array('Excellent damage rate +10%', 'Increase damage +level/20', 'Increase damage +2%', 'Increase Attacking (wizardry) speed +7', 'Increases acquisition rate of life after hunting monsters +life/8', 'Increases acquisition rate of mana after hunting monsters +mana/8');
        $arr[1] = $arr[0];
        $arr[2] = $arr[0];
        $arr[3] = $arr[0];
        $arr[4] = $arr[0];
        $arr[5] = $arr[0];
        //Щиты
        $arr[6] = Array('Increase max HP +4%', 'Increase max Mana +4%', 'Damage decrease +4%', 'Reflect damage +5%', 'Defense success rate +10%', 'Increases acquisition rate of Zen after hunting monsters +40%');
        //Доспехи
        $arr[7] = $arr[6];
        $arr[8] = $arr[6];
        $arr[9] = $arr[6];
        $arr[10] = $arr[6];
        $arr[11] = $arr[6];
        //Accessories
        $arr[12] = Array('Increase Max HP +' . (50 + 5 * $level), 'Increase Max Mana +' . (50 + 5 * $level), "Ignore opponent's defensive power by 3%", 'Max AG +50 increased', 'Increase Command +' . (10 + 5 * $level), 'Increase attacking (wizardry) speed +5');
        //Miscellaneous
        $arr[13] = $arr[6];
        $arr[14] = $arr[13];
        //Свитки
        $arr[15] = 'Нет особых опций';
        return $arr[$type][$id];
    }

    //Массив эффектов скила
    function skill_types() {
        return Array('lvl', 'dmg', 'ReqMana', 'AG', 'Distance', 'Delay', 'Magic', 'ReqLvl');
    }

    //Тип гармонии для типа предмета
    function itemType2HarmonyType($type) {
        $arr = Array();
        //Оружие физическое
        $arr[0] = 1;
        $arr[1] = 1;
        $arr[2] = 1;
        $arr[3] = 1;
        $arr[4] = 1;
        //Оружие магическое
        $arr[5] = 2;
        //Щиты
        $arr[6] = 3;
        //Доспехи
        $arr[7] = 3;
        $arr[8] = 3;
        $arr[9] = 3;
        $arr[10] = 3;
        $arr[11] = 3;
        //Accessories
        $arr[12] = 0;
        //Miscellaneous
        $arr[13] = 0;
        $arr[14] = 0;
        //Свитки
        $arr[15] = 0;
        return $arr[$type];
    }

}

?>
