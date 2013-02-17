<?php

class DebugPDO extends PDO {

    protected
            $query_count = 0,
            $exec_time = 0,
            $log_on = false,
            $logs_arr = Array();
    public $cache = false;

    public function __construct($dsn, $username = null, $password = null, $driver_options = array()) {
        parent::__construct($dsn, $username, $password, $driver_options);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (!$this->getAttribute(PDO::ATTR_PERSISTENT)) {
            $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('DebugPDOStatement', array($this)));
        }
    }

    public function increment_query_count() {
        $this->query_count++;
    }

    public function get_query_count() {
        return $this->query_count;
    }

    public function add_exec_time($time) {
        $this->exec_time += $time;
    }

    public function get_exec_time_ms() {
        return $this->exec_time;
    }

    public function log() {
        if (!$this->log_on) {
            return false;
        }
        $this->logs_arr[] = func_get_args();
    }

    function log_on() {
        $this->log_on = true;
    }

    function log_off() {
        $this->log_on = false;
    }

    function get_log() {
        return $this->logs_arr;
    }

    public function exec($sql) {

        $this->increment_query_count();

        $start = microtime(true);
        $return = parent::exec($sql);
        $finish = microtime(true);
        $time = $finish - $start;
        $this->add_exec_time($time);
        $this->log($sql, 'exec', $time);
        return $return;
    }

    public function query() {

        $args = func_get_args();
        $this->increment_query_count();

        $start = microtime(true);
        
        $return = call_user_func_array(array($this, 'parent::query'), $args);
        
        $finish = microtime(true);
        $time = $finish - $start;
        $this->add_exec_time($time);
        $this->log($args, 'query', $time);

        return $return;
    }

    public function get($sql, $cache_time = false) {
        if ($cache_time) {
           
            if ($arr = $this->cache->get($sql)) {
                return $arr;
            } else {
                $res = $this->query($sql);
                if (!$arr = $res->fetchAll()) {
                    return false;
                }
                $this->cache->set($sql, $arr, $cache_time);
                return $arr;
            }
        } else {
            $res = $this->query($sql);
            return $res->fetchAll();
        }
    }
}

class DebugPDOStatement extends PDOStatement {

    protected
    $pdo;
    private
    $params = array();
    protected static $type_map = array(
        PDO::PARAM_BOOL => "PDO::PARAM_BOOL",
        PDO::PARAM_INT => "PDO::PARAM_INT",
        PDO::PARAM_STR => "PDO::PARAM_STR",
        PDO::PARAM_LOB => "PDO::PARAM_LOB",
        PDO::PARAM_NULL => "PDO::PARAM_NULL"
    );

    protected function __construct(DebugPDO $pdo) {
        $this->pdo = $pdo;
    }

    public function execute($input_parameters = null) {

        $this->pdo->increment_query_count();

        $start = microtime(true);
        $return = parent::execute($input_parameters);
        $finish = microtime(true);
        $time = $finish - $start;
        $this->pdo->add_exec_time($time);

        $this->pdo->log($this->queryString, 'execute', $time);
        if (!empty($this->params)) {
            $this->pdo->log($this->params, 'parameters', $time);
        }
        if (!empty($input_parameters)) {
            $this->pdo->log($input_parameters, 'parameters', $time);
        }

        return $return;
    }

    public function bindValue($pos, $value, $type = PDO::PARAM_STR) {
        $type_name = isset(self::$type_map[$type]) ? self::$type_map[$type] : '(default)';
        $this->params[] = array($pos, $value, $type_name);
        $return = parent::bindValue($pos, $value, $type);
        return $return;
    }

}

?>