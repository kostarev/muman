<?php

Class Controller_menu Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);
        
        SiteRead::me()->access('menu-editor');
    }

    public function index() {
        $this->des->set('title', 'Редактор меню');

        if (isset($this->args[0])) {
            $name = $this->args[0];
            if ($name !== '0') {
                if (!$menu_arr = $this->des->menu->get_punkt($name)) {
                    $this->error('Пункт меню не найден');
                }
                $this->des->set('menu_arr', $menu_arr);
            }

            //Сохранение---
            if (isset($_POST['title'])) {
                $this->des->menu->set($name, $menu_arr['mother'], $_POST['title'], $_POST['url'],$_POST['access']);
                if ($_POST['name'] <> 'name') {
                    $res = $this->db->prepare("UPDATE mm_menu SET name=? WHERE name=?;");
                    $res->execute(Array($_POST['name'], $name));
                }
                $this->loc(H . '/panel/menu/' . $_POST['name']);
            }
            //-------------
        }

        if (isset($this->args[1])) {
            //Поднятие вверх
            if ($this->args[1] == 'up') {
                try {
                    $this->des->menu->up($name);
                    $this->loc(H . '/panel/menu');
                } catch (Exception $e) {
                    $this->error($e->getMessage(), false);
                }
            }
            //Опускание вниз
            elseif ($this->args[1] == 'down') {
                try {
                    $this->des->menu->down($name);
                    $this->loc(H . '/panel/menu');
                } catch (Exception $e) {
                    $this->error($e->getMessage(), false);
                }
            }
            //Добавление пункта
            elseif ($this->args[1] == 'add') {
                try {
                    $this->des->menu->set('new', $name, 'Новый пункт', 'new_url');
                    $this->loc(H . '/panel/menu/new');
                } catch (Exception $e) {
                    $this->error($e->getMessage(), false);
                }
            }
            //Удаление пункта
            elseif ($this->args[1] == 'del') {
                try {
                    $this->des->menu->del($name);
                    $this->loc(H . '/panel/menu');
                } catch (Exception $e) {
                    $this->error($e->getMessage(), false);
                }
            }
        }

        $this->des->set('menu_tree', $this->menu_tree());
        $this->des->display('panel/menu');
    }

    //Многомерный массив меню + html дерево
    function menu_tree($mother = 0) {
        static $res, $res_get;

        $menu = Array('tree' => Array(), 'html' => '');
        if (!isset($res)) {
            $res = $this->db->prepare("SELECT COUNT(*) AS cnt FROM mm_menu WHERE mother=?;");
        }
        $res->execute(Array($mother));
        $max = ($row = $res->fetch()) ? $row['cnt'] : 0;

        if (!isset($res_get)) {
            $res_get = $this->db->prepare("SELECT * FROM mm_menu WHERE mother=? ORDER BY pos;");
        }
        $res_get->execute(Array($mother));
        $rows = $res_get->fetchAll();
        $i = 0;
        foreach ($rows AS $row) {
            $menu['html'].='<div><b>' . ($row['pos'] + 1) . '</b> <span title="' . $row['name'] . '"><a href="' . H . '/panel/menu/' . $row['name'] . '">' . $row['title'] . '</a></span>';
            $i++;
            if ($i > 1) {
                $menu['html'].=' [<a title="Вверх" href="' . H . '/panel/menu/' . $row['name'] . '/up">UP</a>]';
            }

            if ($i < $max) {
                $menu['html'].=' [<a title="Вниз" href="' . H . '/panel/menu/' . $row['name'] . '/down">DN</a>]';
            }

            $menu['html'].=' [<a title="Добавить" href="' . H . '/panel/menu/' . $row['name'] . '/add">+</a>]
                 [<a title="Удалить" class="red" href="' . H . '/panel/menu/' . $row['name'] . '/del">x</a>]';

            $arr = $this->menu_tree($row['name']);
            $row['child'] = $arr['tree'];
            $menu['tree'][$row['name']] = $row;
            $menu['html'].=$arr['html'] . '</div>';
            if ($i == $max) {
                $menu['html'].='<div><a title="Добавить" href="' . H . '/panel/menu/' . $row['mother'] . '/add">[+]</a></div>';
            }
        }
        return $menu;
    }

}

?>
