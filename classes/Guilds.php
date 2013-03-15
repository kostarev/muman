<?php

Class Guilds extends CMS_System{

    //Одиночка паттерн------
    static protected $instance = null;
    //Метод предоставляет доступ к объекту
    static public function me(){
        if (is_null(self::$instance))
            self::$instance = new Guilds();
        return self::$instance;
    }
    
    protected function __construct() {
        parent::__construct();
    }
    //------------------------

    
     //Топ гильдий
    function get_guilds_top($order) {
        $chars = $this->db->get("SELECT TOP 100 [Guild].*, 
            (SELECT COUNT([GuildMember].[Name]) 
            FROM [GuildMember] 
            WHERE [GuildMember].[G_Name] = [Guild].[G_Name]) AS cnt
      FROM [Guild] 
      ORDER BY $order;", 30);
        return $chars;
    }

    //Члены гильдии
    function get_guild_members($id) {
        $res = $this->db->prepare("SELECT MEMB_INFO.memb_guid, MEMB_INFO.memb_name 
            FROM GuildMember
            JOIN MEMB_INFO ON MEMB_INFO.memb___id = GuildMember.Name
            WHERE GuildMember.G_Name =?;
            ");
        $res->execute(Array($id));
        if ($members = $res->fetchAll()) {
            return $members;
        }
        return Array();
    }
}

?>
