<?php

Class Controller_design Extends Controller_Base {

    public function __construct($args) {
        parent::__construct($args);

        SiteRead::me()->access('panel-settings');
    }

    function index() {
        //Получаем список дизайнов
        $dir = D.'/des';
        $arr = scandir($dir);
        $designes = Array();
        foreach($arr AS $val){
            if($val[0]=='.'){
                continue;
            }
            if(is_dir($dir.'/'.$val)){
                if(!is_file($dir.'/'.$val.'/info.ini')){
                    continue;
                }
                $info = parse_ini_file($dir.'/'.$val.'/info.ini');
                $info['dir'] = $val;
                $designes[$val] = $info;
            }
        }
        
            //Выбор темы
            if(isset($_POST['des']) AND isset($designes[$_POST['des']])){
                SiteWrite::me()->save_conf(0,'des',$_POST['des']);
                $this->loc(H.'/panel/design');
            }
        
        $this->des->set('designes',$designes);
        $this->des->set('title', 'Панель - Тема оформления');
        $this->des->display('panel/design');
    }

}

?>