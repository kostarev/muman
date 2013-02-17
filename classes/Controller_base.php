<?php

Abstract Class Controller_Base extends CMS_System {

    protected $args = Array();

    public function __construct($args) {
        parent::__construct();
        $this->args = $args;
        $this->registry['args'] = $args;
    }

    abstract function index();

    //Перенаправление----
    function loc($url = '') {
        if (!$url) {
            $url = H;
        }
        
        if(isset($_SESSION['backurl']) AND $url_key=  array_search($url, $_SESSION['backurl'])){
            unset($_SESSION['backurl'][$url_key]);
        }
        
        header('location:' . $url);
        $this->registry['des']->set('url', $url);
        $this->registry['des']->set('meta', '<meta http-equiv="refresh" content="5; url=' . $url . '">');
        $this->registry['des']->display('_loc');
        exit;
    }

    //-------------------
}

?>