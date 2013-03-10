<?php

//Данные для доступа к Базе Данных
define('DB_SERVER', '{DB_SERVER}');
define('DB_USER', '{DB_USER}');
define('DB_PASSWORD', '{DB_PASSWORD}');
define('DB_NAME', '{DB_NAME}');
define('DB_DRIVER', '{DB_DRIVER}');

//Язык
define('LANGUAGE', 'ru');

//Используется ли md5 для шифрования пароля
define('MD5', {MD5});
//Сезон
define('SEASON', {SEASON});
//колонка Reset
define('RESET', 'reset');
//Кэширование
define('CACHE', true);
//Отключайте только если скрипт установлен не на личном сервере, либо для разработки
define('MEMCACHE_CRYPT', true);
?>