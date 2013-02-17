<?php

Class Controller_sql Extends Controller_Base {

    function index() {
        
        /*
        if(isset($_POST['sql'])){
            $sql = $_POST['sql'];
            $this->db->query($sql);
            $this->des->set('sql', $sql);
        }
        */
        
        if(isset($_POST['title'])){
            $res = $this->db->prepare("UPDATE mm_config SET title=? WHERE name=?;");
            $res->execute(Array($_POST['title'],$_POST['name']));
        }

        $this->des->set('title', 'SQL');
        $this->des->display('sql');

    }

}

?>