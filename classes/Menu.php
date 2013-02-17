<?php

Class Menu extends CMS_System {

    private $full_array = Array();

    function __construct() {
        parent::__construct();
        $arr = $this->db->get("SELECT * FROM mm_menu ORDER BY mother,pos;", 60);
        $this->full_array = $arr;
    }

    public function get($mother) {
        $mother = (String) $mother;
        $arr = Array();

        foreach ($this->full_array AS $val) {
            if ($val['mother'] <> $mother) {
                continue;
            }
            $access_arr = explode(',', $val['access']);

            if ($access_arr AND $this->user['mm_group'] <> 'root') {
                foreach ($access_arr AS $access) {
                    if (!$access) {
                        continue;
                    } elseif ($access == 'user') {
                        if (!$this->user['id']) {
                            continue(2);
                        }
                    } elseif (!SiteRead::me()->is_access($access)) {
                        continue(2);
                    }
                }
            }

            preg_match_all('|{([a-zA-Z0-9]+)->([a-zA-Z0-9]+)}|', $val['url'], $match);

            if ($match[1]) {
                $m = $this->$match[1][0];
                $val['url'] = str_replace($match[0][0], $m[$match[2][0]], $val['url']);
            }
           // $val['url'] = H . '/' . $val['url'];
            
            $arr[] = $val;
        }

        return $arr;
    }

    //Получаем информацию об одном пункте
    function get_punkt($name) {
        $res = $this->db->prepare("SELECT * FROM mm_menu WHERE name=?;");
        $res->execute(Array($name));
        return $res->fetch();
    }

    //html код менюшки, по шаблону
    function get_html($mother, $template, $first_template = false) {
        $html = '';
        $arr = $this->get($mother);
        $first = true;
        $i = 0;
        foreach ($arr AS $val) {
            $i++;
            if ($first AND $first_template) {
                $html.=str_replace(Array('{pos}', '{title}', '{url}', '{name}', '{access}', '{mother}', '{first}'), Array($i, $val['title'], $val['url'], $val['name'], $val['access'], $val['mother'], true), $first_template);
            } else {
                $html.=str_replace(Array('{pos}', '{title}', '{url}', '{name}', '{access}', '{mother}'), Array($i, $val['title'], $val['url'], $val['name'], $val['access'], $val['mother']), $template);
            }
            $first = false;
        }
        return $html;
    }

    //html код дерева менюшки, по шаблону
    function get_tree_html($mother, $li_class) {
        if (!$arr = $this->get($mother)) {
            return '';
        }
        $html = '';
        $child_class = ($li_class == 'class="top"') ? ' class="sub"' : '';
        foreach ($arr AS $val) {
            $span_class = '';
            $a_class = ($li_class == 'class="top"') ? ' class="top_link"' : '';
            $i++;
            if ($child_html = $this->get_tree_html($val['name'])) {
                $child_html = '<ul' . $child_class . '>' . $child_html . '</ul>';
                $span_class = ($li_class == 'class="top"') ? ' class="down"' : ' class="right"';
                $a_class = ($li_class <> 'class="top"') ? ' class="fly"' : ' class="top_link"';
            }
            $html.='<li ' . $li_class . '><a' . $a_class . ' href="' . $val['url'] . '"><span' . $span_class . '>' . $val['title'] . '</span></a>' . $child_html . '</li>';
        }
        $html.='';
        return $html;
    }

    //Добавление/редактирование пункта в меню
    function set($name, $mother, $title, $url, $access = '') {
        $title = Func::filtr($title);
        $res = $this->db->prepare("SELECT name FROM mm_menu WHERE name=?;");
        $res->execute(Array($name));
        if ($row = $res->fetch()) {
            $res = $this->db->prepare("UPDATE mm_menu SET mother=?,title=?,url=?,access=? WHERE name=?;");
            $res->execute(Array($mother, $title, $url, $access, $name));
        } else {
            $res = $this->db->prepare("INSERT INTO mm_menu (mother,name,title,url,access,pos) VALUES (?,?,?,?,?,100);");
            $res->execute(Array($mother, $name, $title, $url, $access));
            $this->update_pos($mother);
        }
        $this->cache->flush();
    }

    //Удаление пункта меню и всех вложенных пунктов
    function del($name) {
        $res = $this->db->prepare("SELECT name FROM mm_menu WHERE mother=?;");
        $res->execute(Array($name));
        while ($row = $res->fetch()) {
            $this->del($row['name']);
        }

        $res = $this->db->prepare("DELETE FROM mm_menu WHERE name=?;");
        $res->execute(Array($name));
        $this->cache->flush();
    }

    //Поднятие позиции пункта меню
    function up($name) {
        $res = $this->db->prepare("SELECT mother,pos FROM mm_menu WHERE name=?;");
        $res->execute(Array($name));
        if (!$row = $res->fetch()) {
            throw new Exception('Пункт меню не найден');
        }

        $res_update = $this->db->prepare("UPDATE mm_menu SET pos=? WHERE name=?;");

        $pos = $row['pos'];
        $mother = $row['mother'];
        $new_pos = $pos - 1;
        $res = $this->db->prepare("SELECT name FROM mm_menu WHERE pos=? AND mother=?;");
        $res->execute(Array($new_pos, $mother));
        if ($row = $res->fetch()) {
            $res_update->execute(Array($pos, $row['name']));
        }
        $res_update->execute(Array($new_pos, $name));
        $this->update_pos($mother);
        $this->cache->flush();
    }

    //Поднятие позиции пункта меню
    function down($name) {
        $res = $this->db->prepare("SELECT mother,pos FROM mm_menu WHERE name=?;");
        $res->execute(Array($name));
        if (!$row = $res->fetch()) {
            throw new Exception('Пункт меню не найден');
        }

        $res_update = $this->db->prepare("UPDATE mm_menu SET pos=? WHERE name=?;");

        $pos = $row['pos'];
        $mother = $row['mother'];
        $new_pos = $pos + 1;
        $res = $this->db->prepare("SELECT name FROM mm_menu WHERE pos=? AND mother=?;");
        $res->execute(Array($new_pos, $mother));
        if ($row = $res->fetch()) {
            $res_update->execute(Array($pos, $row['name']));
        }
        $res_update->execute(Array($new_pos, $name));
        $this->update_pos($mother);
        $this->cache->flush();
    }

    //Пересчёт позиций пунктов меню
    function update_pos($mother) {
        $res_update = $this->db->prepare("UPDATE mm_menu SET pos=? WHERE name=?;");
        $res = $this->db->prepare("SELECT name FROM mm_menu WHERE mother=? ORDER BY pos;");
        $res->execute(Array($mother));
        $i = 0;
        $rows = $res->fetchAll();
        foreach ($rows AS $row) {
            $res_update->execute(Array($i, $row['name']));
            $i++;
        }
    }

}

?>
