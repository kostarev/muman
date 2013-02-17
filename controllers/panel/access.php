<?php

Class Controller_access Extends Controller_Base {
    
    public function __construct($args) {
        parent::__construct($args);
        
        if($this->user['mm_group']<>'root'){
            $this->error('Ошибка доступа');
        }
    }

    public function index() {

        //Обработка формы-------
        if (isset($_POST['save'])) {

            foreach ($this->groups AS $gr) {
                foreach ($this->actions AS $act) {
                    
                    try{
                        
                        SiteWrite::me()->save_access($gr['name'], $act['name'], isset($_POST[$gr['name']][$act['name']]));
                    }  catch (Exception $e){$this->error($e->getMessage());}
                    
                }
            }
            $this->loc(H.'/panel/access');
        }
        //----------------------

        
        $this->des->set('title', 'Настройки доступа');
        $this->des->set('title_html', '<a href="'.H.'/panel">Панель</a> - Настройки доступа');
        $this->des->display('panel/access');
    }

}

?>
