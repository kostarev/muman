<?php
Class Chars extends CMS_System {
    
    //Одиночка паттерн------
    static protected $instance = null;
    //Метод предоставляет доступ к объекту
    static public function me(){
        if (is_null(self::$instance))
            self::$instance = new Chars();
        return self::$instance;
    }
    
    protected function __construct() {
        parent::__construct();
    }
    //------------------------
    //Получение персонажей юзера
    function get_user_chars($login) {

        static $arr;
        if (isset($arr[$login])) {
            return $arr[$login];
        }

        $res = $this->db->prepare("SELECT name,AccountID,class,strength,dexterity,vitality,energy,money,mapnumber,clevel," . RESET . ",LevelUpPoint,pkcount,pklevel,leadership,experience,ctlcode
            FROM [Character] WHERE AccountID = ?;");
        $res->execute(Array($login));
        if ($rows = $res->fetchAll()) {
            foreach ($rows AS $key => $val) {
                $rows[$key]['class_title'] = $this->get_class($val['class']);
            }
            $arr[$login] = $rows;
        } else {
            $arr[$login] = Array();
            return $arr[$login];
        }

        return $arr[$login];
    }

    //Получение персонажа
    function get_char($Name) {
        static $arr;
        if (isset($arr[$Name])) {
            return $arr[$Name];
        }
        $res = $this->db->prepare("SELECT name,AccountID,class,strength,dexterity,vitality,energy,money,mapnumber,clevel," . RESET . " AS reset,LevelUpPoint,pkcount,pklevel,leadership,experience,ctlcode
            FROM [Character] WHERE Name = ?;");
        $res->execute(Array($Name));
        if (!$row = $res->fetch()) {
            throw new Exception('Персонаж не найден в базе данных');
        }

        $row['class_title'] = $this->get_class($row['class']);
        $arr[$Name] = $row;

        return $arr[$Name];
    }
    
    //Сохранение изменений персонажа
    function save($id, $arr){
        $res = $this->db->prepare("UPDATE [Character] SET AccountID=?,class=?,strength=?,dexterity=?,vitality=?,energy=?,money=?,mapnumber=?,clevel=?," . RESET . " =?,LevelUpPoint=?,pkcount=?,pklevel=?,leadership=?,experience=?,ctlcode=? WHERE Name = ?;");
        $res -> execute(Array($arr['AccountID'],$arr['class'],$arr['strength'],$arr['dexterity'],$arr['vitality'],$arr['energy'],$arr['money'],$arr['mapnumber'],$arr['clevel'],$arr['reset'],$arr['LevelUpPoint'],$arr['pkcount'],$arr['pklevel'],$arr['leadership'],$arr['experience'],$arr['ctlcode'],$arr['name']));
    }

    //Получение класса персонажа из цифры
    function get_class($value) {
        $class[0]['name'] = 'Dark Wizard';
        $class[0]['img'] = 'dw';
        $class[1]['name'] = 'Soul Master';
        $class[1]['img'] = 'dw';
        $class[2]['name'] = 'Grand Master';
        $class[2]['img'] = 'dw';
        $class[3]['name'] = 'Grand Master';
        $class[3]['img'] = 'dw';
        $class[16]['name'] = 'Dark Knight';
        $class[16]['img'] = 'dk';
        $class[17]['name'] = 'Blade Knight';
        $class[17]['img'] = 'dk';
        $class[18]['name'] = 'Blade Master';
        $class[18]['img'] = 'dk';
        $class[19]['name'] = 'Blade Master';
        $class[19]['img'] = 'dk';
        $class[32]['name'] = 'Fairy Elf';
        $class[32]['img'] = 'me';
        $class[33]['name'] = 'Muse Elf';
        $class[33]['img'] = 'me';
        $class[34]['name'] = 'High Elf';
        $class[34]['img'] = 'me';
        $class[35]['name'] = 'High Elf';
        $class[35]['img'] = 'me';
        $class[48]['name'] = 'Magic Gladiator';
        $class[48]['img'] = 'mg';
        $class[50]['name'] = 'Duel Master';
        $class[50]['img'] = 'mg';
        $class[64]['name'] = 'Dark Lord';
        $class[64]['img'] = 'dl';
        $class[66]['name'] = 'Lord Emperor';
        $class[66]['img'] = 'dl';
        $class[80]['name'] = 'Summoner';
        $class[80]['img'] = 'sm';
        $class[81]['name'] = 'Bloody Summoner';
        $class[81]['img'] = 'sm';
        $class[82]['name'] = 'Dimension Master';
        $class[82]['img'] = 'sm';
        $class[83]['name'] = 'Dimension Master';
        $class[83]['img'] = 'sm';
        $class[96]['name'] = 'Rage Fighter';
        $class[96]['img'] = 'rf';

        return isset($class[$value]) ? $class[$value] : Array('name' => 'Unknown', 'img' => 0);
    }

    //Топ персонажей
    function get_top($order) {
        $chars = $this->db->get("SELECT TOP 100 name,AccountID,class,strength,dexterity,vitality,energy,money,mapnumber,clevel," . RESET . " AS reset,LevelUpPoint,pkcount,pklevel,leadership,experience,ctlcode
            FROM [Character] ORDER BY $order;", 30);
        return $chars;
    }


    //Перераспределение свободных очков
    function levelUpManager($arr) {
        $char = $arr['char'];
        if (!$pers = $this->get_char($char)) {
            throw new Exception('Персонаж не найден');
        }

        $LevelUpPoint = $pers['LevelUpPoint'];
        $strength = abs((int) $arr['strength']);
        $energy = abs((int) $arr['energy']);
        $vitality = abs((int) $arr['vitality']);
        $dexterity = abs((int) $arr['dexterity']);
        $leadership = abs((int) $arr['leadership']);

        $summa = $strength + $energy + $vitality + $dexterity + $leadership;
        if ($LevelUpPoint < $summa) {
            throw new Exception('Сумма параметров больше свободных очков');
        }


        $res = $this->db->prepare("UPDATE Character SET strength=strength+?,energy=energy+?,vitality=vitality+?,dexterity=dexterity+?,leadership=leadership+?,LevelUpPoint=LevelUpPoint-? WHERE Name=?;");
        $res->execute(Array($strength, $energy, $vitality, $dexterity, $leadership, $summa, $pers['name']));
    }

     //Изменяем деньги персонажа
    function char_money($Name, $money) {
        $money = (int) $money;
        if (!$pers = $this->get_char($Name)) {
            throw new Exception('Персонаж не найден');
        }
        $money_was = $pers['money'];
        if($money_was+$money >2000000000){
            throw new Exception('Персонажу нельзя дать больше 2 000 000 000 Zen');
        }
        $res = $this->db->prepare("UPDATE Character SET Money=Money+? WHERE Name=?;");
        $res->execute(Array($money, $Name));
    }
}

?>
