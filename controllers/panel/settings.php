<?php

Class Controller_Settings Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);
        
        SiteRead::me()->access('panel-settings');
    }

    function index() {
        $title = '';
        $settings_dir = isset($this->args[0]) ? $this->args[0] : false;
        $title_html = ' - Настройки';
        $configs = Array();
        if ($settings_dir) {
            if (!isset($this->conf_array[$settings_dir])) {
                $this->error('Раздел настроек ' . $settings_dir . ' не найден.');
            } elseif($this->conf_dirs[$settings_dir]['group'] AND $this->conf_dirs[$settings_dir]['group']<>$this->user['mm_group'] AND $this->user['mm_group']<>'root'){
                $this->error('У вас нет доступа к редактированию этих настроек.');
            }else{
                $title = ' - ' . $this->conf_dirs[$settings_dir]['title'];
                $title_html = ' - <a href="' . H . '/panel/settings">Настройки</a>' . $title;
                $configs = $this->conf_array[$settings_dir];
            }

            //Обработка формы-----------
            if (isset($_POST['save'])) {

                foreach ($configs AS $key => $val) {
                    if (!isset($_POST['conf'][$key])) {
                        $configs[$key]['value'] = 0;
                    } else {
                        $configs[$key]['value'] = $_POST['conf'][$key];
                    }

                    if ($val['type'] == 'int') {
                        $configs[$key]['value'] = (real) $configs[$key]['value'];
                    }

                    
                    SiteWrite::me()->save_conf($settings_dir, $key, $configs[$key]['value']);
                }

                $this->loc(H . '/panel/settings/' . $settings_dir);
            }
            //--------------------------
        }
        
        $this->des->set('settings_dir', $settings_dir);
        $this->des->set('configs', $configs);
        $this->des->set('title', 'Панель - Настройки' . $title);
        $this->des->set('title_html', '<a href="' . H . '/panel">Панель</a>' . $title_html);
        $this->des->display('panel/settings');
    }

    function db_log_off() {
        if ($this->user['mm_group'] <> 'root') {
            $this->error('Доступ закрыт');
        }
        
        SiteWrite::me()->save_conf('developer', 'sql_table', 0);
        $this->loc($this->back_url);
    }

    function memcache_table_off() {
        if ($this->user['mm_group'] <> 'root') {
            $this->error('Доступ закрыт');
        }
        
        SiteWrite::me()->save_conf('developer', 'memcache_table', 0);
        $this->loc($this->back_url);
    }

    function del_memcached() {

        if ($this->user['mm_group'] <> 'root') {
            $this->error('Доступ закрыт');
        }

        if (!isset($this->args[0])) {
            $this->error('Не верная ссылка');
        }
        if ($this->cache) {
            $this->cache->delete($this->args[0]);
        }
        $this->loc($this->back_url);
    }

}

?>