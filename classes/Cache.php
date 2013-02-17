<?php

Class Cache {

    protected $mem;

    function __construct() {
        if (class_exists('Memcache') AND CACHE) {
            $this->mem = new Memcache;
            $this->mem->connect('127.0.0.1', 11211);
        }
    }

    //Берём кэш по ключу
    function get($key) {
        if ($this->mem) {
            if (MEMCACHE_CRYPT) {
                $cache_key = md5(DB_PASSWORD . DB_NAME . $key);
            } else {
                $cache_key = $key;
            }
            if(!$arr = $this->mem->get($cache_key)){
                return false;
            }
            if (MEMCACHE_CRYPT) {
                $arr = unserialize(Crypt::decode($arr, DB_SERVER . DB_USER . DB_PASSWORD));
            }
            return $arr;
        }
        return false;
    }

    //Записываем кэш с ключём и временем жизни
    function set($key, $value, $liveTime = false) {
        if ($this->mem) {
            $crypt_arr = $value;
            if (MEMCACHE_CRYPT) {
                $cache_key = md5(DB_PASSWORD . DB_NAME . $key);
                $crypt_arr = Crypt::encode(serialize($crypt_arr), DB_SERVER . DB_USER . DB_PASSWORD);
            }else{
                $cache_key = $key;
            }
            return $this->mem->set($cache_key, $crypt_arr, false, $liveTime);
        }
        return false;
    }

    //Сброс кэша
    function flush() {
        if ($this->mem) {
            return $this->mem->flush();
        }
        return false;
    }

}

?>
