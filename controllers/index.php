<?php

Class Controller_Index Extends Controller_Base {

    function index() {

        $this->des->set('title', 'Mu Manager CMS');
        $this->des->display('index');

    }

}

?>