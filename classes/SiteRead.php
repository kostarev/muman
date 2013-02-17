<?php
Class SiteRead Extends CMS_System {
    
    //Одиночка паттерн------
    static protected $instance = null;
    //Метод предоставляет доступ к объекту
    static public function me(){
        if (is_null(self::$instance))
            self::$instance = new SiteRead();
        return self::$instance;
    }
    
    protected function __construct() {
        parent::__construct();
    }
    //------------------------

    //Проверка доступа группе--
    function is_group_access($group, $action) {
        if ($group == 'root') {
            return true;
        }
        return isset($this->groups[$group]['actions_arr'][$action]);
    }

    //------------------------
    //Проверка доступа группе--
    function group_access($group, $action) {
        if (!$this->is_group_access($group, $action)) {
            $this->error('Доступ закрыт');
        }
        return true;
    }

    //------------------------
    //Возвращает true или false
    function is_access($action) {
        return $this->is_group_access($this->user['mm_group'], $action);
    }

    //Выбивает ошибку, если доступ запрещён
    function access($action) {
        if (!$this->is_access($action)) {
            $this->error('Доступ закрыт');
        }
        return true;
    }

    //Авторизация-----------
    function auth($login, $pas) {
        $res = $this->db->prepare("SELECT memb_guid AS id, memb__pwd FROM MEMB_INFO WHERE memb___id=?;");
        $res->execute(Array(trim($login)));
        if (!$row = $res->fetch()) {
            throw new Exception("Пользователь с логином $login не найден.");
        }

        if(MD5){
        $res =$this->db->prepare("SELECT [dbo].[fn_md5](?,?) AS pas;");
        $res->execute(Array($pas,$login));
        $arr = $res->fetch();
        $md5pas =$arr['pas'];
        }else{
        $md5pas = $pas;
        }

        if ($md5pas <> $row['memb__pwd']) {
            throw new Exception('Не верный пароль.<br /><a href="' . H . '/login/forget">Забыли пароль?</a>');
        }

        return Array('id' => $row['id'], 'pas' => $md5pas);
    }

    //----------------------

    function dbLog2html() {
        $html = '  
<style type="text/css">
table.sys th{background-color: #9999ff; padding:7px; text-align: center;}
table.sys td{background-color: #ccccff; padding:7px; text-align: center;}
table.sys td.left{text-align: left;}
.red{color:red;}
</style>
Data Base
<table class="sys" style="width:100%;">
<tr><th style="width:50px;">№</th><th>SQL <span style="position:absolute;right:110px;"><a title="Закрыть таблицу" href="' . H . '/panel/settings/db_log_off">[X]</a></span></th><th style="width:50px;">Time</th></tr>';
        $arr = $this->db->get_log();
        $i = 0;
        $max_time = 0;
        foreach ($arr AS $val) {
            $max_time = $val[2] > $max_time ? $val[2] : $max_time;
        }

        $hl = new highlightSQL;
        foreach ($arr AS $val) {
            $time = round($val[2], 4);
            if ($val[1] == 'query') {
                $i++;
                $sql = nl2br($hl->highlight($val[0][0]));
                $num = $i;
                $t = '<td ' . (($val[2] == $max_time) ? 'class="red"' : '') . '>' . $time . '</td>';
            } elseif ($val[1] == 'execute') {
                $i++;
                $sql = nl2br($hl->highlight($val[0]));
                $num = $i;
                $t = '<td ' . (($val[2] == $max_time) ? 'class="red"' : '') . '>' . $time . '</td>';
            } elseif ($val[1] == 'parameters') {
                $sql = implode('|', $val[0]);
                $num = '&gt;';
                $t = '<td>&nbsp;</td>';
            }
            $html.='<tr><td>' . $num . '</td><td class="left">' . $sql . '</td>' . $t . '</tr>';
        }
        $html .= '</table>';
        return $html;
    }

    function memCache2html() {
        $html = '';
        if (!class_exists('Memcache')) {
            return '';
        }
        $cache = new Memcache;
        $cache->connect('127.0.0.1', 11211);



        if ($res = $cache->getstats('items') AND !empty($res['items'])) {
            if (MEMCACHE_CRYPT) {
                return '<b>Memcache</b>: Для отображения таблицы, отключите шифрование кэша в sys/config.php <b>MEMCACHE_CRYPT</b>';
            }
            $html.='<b>Memcache</b>. В целях безопасности включите шифрование кэша в sys/config.php <b>MEMCACHE_CRYPT</b>.
                    <style type="text/css">
table.sys th{background-color: #9999ff; padding:7px; text-align: center;}
table.sys td{background-color: #ccccff; padding:7px; text-align: center;white-space:pre}
table.sys td.left{text-align: left;}
.red{color:red;}
</style><table class="sys" style="width:100%;">
                    <tr><th style="width:50px;">№</th><th style="width:80px;">Ключ</th><th>Значение</th><th>Время жизни</th><th><a href="' . H . '/panel/settings/memcache_table_off" title="Закрыть таблицу" class="red">[X]</a></th></tr>';

            $i = 0;
            foreach ($res['items'] AS $key => $val) {
                $items = $cache->getStats('cachedump', $key);
                foreach ($items AS $key => $val) {
                    $i++;
                    $live_time = $val[1] - TIME;
                    if ($live_time < 0) {
                        $live_time = 'Вечная';
                    } else {
                        $live_time.=' сек.';
                    }
                    $html.='<tr><td>' . $i . '</td><td>' . $key . '</td>
                        <td class="left">' . htmlspecialchars(print_r($cache->get($key), true)) . '</td>
                        <td>' . $live_time . '</td><td><a class="red" title="Удалить" href="' . H . '/panel/settings/del_memcached/' . $key . '">[X]</a></td></tr>';
                }
            }

            $html.='</table>';
        }

        return $html;
    }

    //Вывод текста с обработкой смайлов и бб кодов
    function out($text) {
        $text = Func::links($text);
        $text = $this->registry['bb']->out($text);


        if (strstr($text, '[nosmile]')) {
            $text = str_replace('[nosmile]', '', $text);
        } else {
            $text = $this->registry['smiles']->out($text);
        }

        $text = str_replace("<br />", "\n", $text);
        $text = nl2br($text);
        $text = preg_replace('#[\r\n]#', '', $text);

        return $text;
    }

}

?>
