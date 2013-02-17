<?php

Class Controller_users Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);
        
        SiteRead::me()->access('panel');
    }

    public function index() {

        $on_page = 30;

        $res = $this->db->query("SELECT COUNT(*) AS cnt FROM MEMB_INFO;");
        $kolvo = ($row = $res->fetch()) ? $row['cnt'] : 0;
        $page = isset($this->args[0]) ? (int) $this->args[0] : 1;
        $arr = Func::pages_arr($kolvo, $on_page, $page);
        $max = $arr['max_page'];
        $start = $arr['start'];
        $page = $arr['page'];
        $this->des->set('page',$page);
        $this->des->set('on_page',$on_page);
        $this->des->set('pages', Func::pages($page, $max, H . '/panel/users/{page}'));

        
        /*
        $res = $this->db->query("SELECT t.memb_guid,t.memb___id,t.memb_name,mm_groups.title AS group_title
            FROM (
                 SELECT *,
                 ROW_NUMBER() OVER (ORDER BY MEMB_INFO.memb_guid) AS rownum
                 FROM MEMB_INFO) AS t
            JOIN mm_groups ON mm_groups.name=t.mm_group
            WHERE t.rownum BETWEEN $start+1 AND $start + $on_page
            ORDER BY t.memb_guid;");
        */

       $res = $this->db->query("SELECT num, O.memb_guid,O.memb___id,O.memb_name,C.title AS group_title
   FROM MEMB_INFO O
        INNER JOIN
        (SELECT count(*) AS num, O2.memb_guid
           FROM MEMB_INFO O1
                INNER JOIN mm_groups C1 ON O1.mm_group = C1.name
                INNER JOIN MEMB_INFO O2 ON O1.memb_guid <= O2.memb_guid
                INNER JOIN mm_groups C2 
                  ON O2.mm_group = C2.name 
           GROUP BY O2.memb_guid 
           HAVING count(*) BETWEEN $start+1 AND $start + $on_page
        ) AS OO ON O.memb_guid = OO.memb_guid
        JOIN mm_groups C ON C.name=O.mm_group
   ORDER BY OO.num ASC"); 
        

        $users = $res->fetchAll();
        //print_r($users);

        $this->des->set('users', $users);
        $this->des->set('title', 'Панель - Пользователи');
        $this->des->set('title_html', '<a href="' . H . '/panel">Панель</a> - Пользователи');
        $this->des->display('panel/users');
    }

    //Смена прав пользователя
    function change_group() {
        
        SiteRead::me()->access('change-group');

        if (!isset($this->args[0])) {
            $this->error('Не верная ссылка');
        }

        $user_id = (int) $this->args[0];
        if ($this->user['id'] == $user_id) {
            $this->error('Свою группу изменить нельзя');
        }
        $Anketa = new User($user_id);
        $info = $Anketa->get_info();

        //Обработка формы----
        if (isset($_POST['group'])) {
            try {
                
                SiteWrite::me()->change_user_group($user_id, $_POST['group']);
                $this->loc($this->back_url);
            } catch (Exception $e) {
                $this->error($e->getMessage(), false);
            }
        }
        //-------------------


        $this->des->set('user', $info);
        $this->des->set('change_group', true);
        $this->des->set('title', 'Панель - Пользователи - Смена группы');
        $this->des->set('title_html', '<a href="' . H . '/panel">Панель</a> - <a href="' . H . '/panel/users">Пользователи</a> - ' . $info['memb___id']);
        $this->des->display('user');
        $this->des->display('panel/users');
    }

    //Персонажи пользователя---
    function pers() {
        if (!isset($this->args[0])) {
            $this->error('Не верная ссылка');
        }
        $user_id = (int) $this->args[0];
        $user = new User($user_id);

        try {
            $arr = $user->get_info();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }


        $this->des->set('title', 'Панель - ' . $arr['memb_name'] . ' - Персонажи');
        $this->des->set('title_html', '<a href="' . H . '/panel">Панель</a> - <a href="' . H . '/panel/users">Пользователи</a> - ' . $arr['memb_name'] . ' - Персонажи');


        $chars = Chars::me()->get_user_chars($arr['memb___id']);
        $this->des->set('chars', $chars);

        $this->des->set('user', $arr);
        $this->des->set('pers', true);
        $this->des->display('pers_list');
    }

}

?>
