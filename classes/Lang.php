<?php

Class Lang {

    private $enable;
    private $cache_str;
    private $lang;

    function __construct($language = 'ru') {
        $this->lang = $language;
        $enable = true;
        ob_start();
    }

    //Отключение
    function off() {
        $this->enable = false;
    }

    //Замена
    function replace() {
        $this->cache_str = ob_get_contents();
        ob_end_clean();
        $lang_file = D . '/sys/lang/' . $this->lang . '.php';
        if (is_file($lang_file)) {
            $lang = Array();
            include $lang_file;
            $this->cache_str = str_replace(array_keys($lang), array_values($lang), $this->cache_str);
        }

        echo $this->cache_str;
    }

}

?>
