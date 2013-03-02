<?php

Class Controller_update Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);

        SiteRead::me()->access('root');
    }

    function index() {

        $version = MuMan::me()->version();
        $this->des->set('version', $version);
        $vers = $version .' '. MuMan::me()->version_type();
        try {
            $last_version_arr = MuMan::me()->last_version();
            $last = $last_version_arr['last_version'];
            $last_type = $last_version_arr['last_type'];
            $this->des->set('last', $last);
            $this->des->set('last_type', $last_type);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        
            //Загружаем модуль обновления
            if(isset($_POST['update'])){
                try{
                MuMan::me()->load_update();
                }catch(Exception $e){
                    $this->error($e->getMessage());
                }
                
                //Автоматическая установка и слияние с системой
                if(isset($_POST['merge'])){
                    $smod_file = $version.'-'.$last.'.smod';
                    MuMan::me()->install($smod_file);
                    MuMan::me()->merge($smod_file, true);
                    $this->loc(H.'/panel/update');
                }
                
                $this->loc(H.'/panel/modules/installed');
            }
        
        $this->des->set('vers', $vers);
        $this->des->set('title', 'Панель - Обновление');
        $this->des->display('panel/update');
    }

}

?>