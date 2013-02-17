<?php

Class Controller_Index Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);

        SiteRead::me()->access('panel');
    }

    function index() {

        $this->des->set('title', 'Панель');
        $this->des->display('panel/index');
    }

}

?>