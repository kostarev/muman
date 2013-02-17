<?php

//Данные для доступа к Базе Данных
define('DB_SERVER', 'localhost');
define('DB_USER', 'sa');
define('DB_PASSWORD', '123456');
define('DB_NAME', 'muonline');
define('DB_DRIVER', '{SQL Server Native Client 10.0}');

//Язык
define('LANGUAGE', 'ru');

//Используется ли md5 для шифрования пароля
define('MD5', false);

//колонка Reset
define('RESET', 'reset');
//Кэширование
define('CACHE', false);
//Отключайте только если скрипт установлен не на личном сервере, либо для разработки
define('MEMCACHE_CRYPT', true);
?>