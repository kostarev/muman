<?php

session_start();

// Константы:
define('D', $_SERVER['DOCUMENT_ROOT']);
define('H', str_replace($_SERVER['DOCUMENT_ROOT'], 'http://' . $_SERVER['HTTP_HOST'], D));

$step = isset($_GET['s']) ? (int) $_GET['s'] : 1;

if ($step == 1) {
    if (isset($_POST['DB_SERVER'])) {

        //Пробуем подключиться к бд
        include_once D . '/classes/DebugPDO.php';
        try {
            $options = Array("CharacterSet" => "UTF-8");
            $db = new DebugPDO("odbc:Driver=" . $_POST['DB_DRIVER'] . ";Server=" . $_POST['DB_SERVER'] . ";Database=" . $_POST['DB_NAME'] . ";", $_POST['DB_USER'], $_POST['DB_PASSWORD'], $options);
            $db->setAttribute(PDO :: ATTR_DEFAULT_FETCH_MODE, PDO :: FETCH_ASSOC);
        } catch (Exception $e) {
            echo 'Ошибка соединения с базой данных: ' . $e->getMessage();
            echo '<p><a href="?s=' . $step . '">Назад</a></p>';
            include '_foot.tpl';
            exit;
        }

        $str = file_get_contents('config.tpl');
        $str = str_replace('{DB_DRIVER}', $_POST['DB_DRIVER'], $str);
        $str = str_replace('{DB_SERVER}', $_POST['DB_SERVER'], $str);
        $str = str_replace('{DB_NAME}', $_POST['DB_NAME'], $str);
        $str = str_replace('{DB_USER}', $_POST['DB_USER'], $str);
        $str = str_replace('{DB_PASSWORD}', $_POST['DB_PASSWORD'], $str);

        //Определяем, используется ли MD5
        $res = $db->query("SELECT DATA_TYPE FROM information_schema.columns WHERE TABLE_NAME = 'MEMB_INFO' AND COLUMN_NAME='memb__pwd';");
        if (!$rows = $res->fetchAll()) {
            echo 'Ошибка. Не удалось получить тип столбца memb__pwd из таблицы MEMB_INFO';
            echo '<p><a href="?s=' . $step . '">Назад</a></p>';
            include '_foot.tpl';
            exit;
        }

        //MD5 не используется
        if ($rows[0]['DATA_TYPE'] == 'varchar') {
            $str = str_replace('{MD5}', 'false', $str);
        } else {
            $str = str_replace('{MD5}', 'true', $str);
        }


        //Сохраняем конфиг
        file_put_contents(D . '/sys/config.php', $str);

        $res = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'mm_actions';");
        //Если нет таблицы, создаём ее
        if (!$rows = $res->fetchAll()) {
            $db->query("
  CREATE TABLE mm_actions (
  id int NOT NULL IDENTITY(0, 1),
  [name]  varchar(20) NOT NULL,
  title   nvarchar(50) NOT NULL,
  CONSTRAINT PK_mm_actions PRIMARY KEY (id)
);");

            $res = $db->prepare("INSERT INTO mm_actions ([name], title) VALUES ('panel', ?);");
            $res->execute(Array('Доступ к админке'));
            $res = $db->prepare("INSERT INTO mm_actions ([name], title) VALUES ('panel-settings', ?);");
            $res->execute(Array('Доступ к настройкам'));
            $res = $db->prepare("INSERT INTO mm_actions ([name], title) VALUES ('change-group', ?);");
            $res->execute(Array('Смена группы'));
            $res = $db->prepare("INSERT INTO mm_actions ([name], title) VALUES ('change-modules', ?);");
            $res->execute(Array('Управление модулями'));
            $res = $db->prepare("INSERT INTO mm_actions ([name], title) VALUES ('menu-editor', ?);");
            $res->execute(Array('Редактор меню'));
        }


        $res = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'mm_config';");
        //Если нет таблицы, создаём ее
        if (!$rows = $res->fetchAll()) {
            $db->query("CREATE TABLE mm_config (
  mother   varchar(50),
  [name]   varchar(50) NOT NULL,
  title    nvarchar(100) NOT NULL,
  [type]   varchar(10) NOT NULL,
  [value]  varchar(100),
  [group]  varchar(30)
)");

            $res = $db->prepare("INSERT INTO mm_config (mother, [name], title, [type], [value], [group]) VALUES (?, ?, ?, ?, ?, ?);");
            $res->execute(Array('0','developer','Разработчику','text', 'directory','root'));
            $res->execute(Array('0', 'reg', 'Регистрация', 'text', 'directory', ''));
            $res->execute(Array('0', 'des', 'design', 'text', 'default', ''));
            $res->execute(Array('developer', 'memcache_table', 'Memcache таблица', 'checkbox', '0', ''));
            $res->execute(Array('developer', 'params_table','Данные php роутера', 'checkbox', '0', ''));
            $res->execute(Array('developer', 'sql_table', 'Таблица SQL запросов', 'checkbox', '0', ''));
            $res->execute(Array('developer', 'tpl_borders', 'Границы шаблонов в html комментариях', 'checkbox', '0', ''));
            $res->execute(Array('reg', 'captcha', 'Captcha при регистрации', 'checkbox', '1', ''));
            $res->execute(Array('reg', 'email', 'Поле Email', 'checkbox', '1', ''));
            $res->execute(Array('reg', 'email_must', 'Проверка подлинности Email', 'checkbox', '1', ''));
            $res->execute(Array('reg', 'on', 'Регистрация включена', 'checkbox', '1', ''));
        }


        $res = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'mm_groups';");
        //Если нет таблицы, создаём ее
        if (!$rows = $res->fetchAll()) {
            $db->query("CREATE TABLE mm_groups (
  [name]   varchar(20) NOT NULL,
  title    nvarchar(50) NOT NULL,
  actions  varchar(200)
)");

            $res = $db->prepare("INSERT INTO mm_groups ([name], title) VALUES (?, ?);");
            $res->execute(Array('root','Супер Админ'));
            $res->execute(Array('admin','Админ'));
            $res->execute(Array('user','Пользователь'));
        }

        $res = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'mm_menu';");
        //Если нет таблицы, создаём ее
        if (!$rows = $res->fetchAll()) {
            $db->query("CREATE TABLE mm_menu (
  mother    varchar(30) NOT NULL,
  [name]    varchar(30) NOT NULL,
  title     nvarchar(50) NOT NULL,
  pos       int NOT NULL,
  [access]  varchar(100) NOT NULL,
  url       varchar(255) NOT NULL
)");
            $res = $db->prepare("INSERT INTO mm_menu (mother, [name], title, pos, [access], url) VALUES (?, ?, ?, ?, ?, ?);");
            $res->execute(Array('0', 'main', 'Главная', 0, '', '/'));
            $res->execute(Array('0', 'kabinet', 'Кабинет', 1, 'user', '/kabinet'));
            $res->execute(Array('0', 'panel', 'Админка', 2, 'panel', '/panel'));
            $res->execute(Array('kabinet', 'anketa', 'Анкета', 0, 'user', '/user/{user->id}'));
            $res->execute(Array('kabinet', 'kabinet-pers', 'Персонажи', 1, 'user', '/kabinet/pers'));
            $res->execute(Array('panel', 'settings', 'Настройки', 0, 'panel-settings', '/panel/settings'));
            $res->execute(Array('panel', 'access', 'Права', 1, 'root', '/panel/access'));
            $res->execute(Array('panel', 'modules', 'Модули', 4, 'change-modules', '/panel/modules'));
            $res->execute(Array('panel', 'users', 'Пользователи', 3, 'panel', '/panel/users'));
            $res->execute(Array('panel', 'menu_editor', 'Редактор меню', 2, 'menu-editor', '/panel/menu'));
            $res->execute(Array('panel', 'update', 'Обновление', 5, 'root', '/panel/update'));
            $res->execute(Array('settings', 'set-dev', 'Разработчику', 0, 'root', '/panel/settings/developer'));
            $res->execute(Array('settings', 'design', 'Темы оформления', 1, 'panel-settings', '/panel/design'));
            $res->execute(Array('settings', 'set-reg', 'Регистрация', 1, 'panel-settings', '/panel/settings/reg'));
            $res->execute(Array('modules', 'mod-new', 'Добавить', 1, 'change-modules', '/panel/modules/install'));
            $res->execute(Array('modules', 'mod-installed', 'Загруженные', 0, 'change-modules', '/panel/modules/installed'));
        }

        $res = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'mm_modules';");
        //Если нет таблицы, создаём ее
        if (!$rows = $res->fetchAll()) {
            $db->query("CREATE TABLE mm_modules (
  id int NOT NULL IDENTITY(0, 1),
  fname  varchar(50) NOT NULL,
  CONSTRAINT PK_mm_modules PRIMARY KEY (id)
)");
        }

        $res = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'mm_modules_files';");
        //Если нет таблицы, создаём ее
        if (!$rows = $res->fetchAll()) {
            $db->query("CREATE TABLE mm_modules_files (
  id      int NOT NULL IDENTITY(0, 1),
  module  int NOT NULL,
  fname   varchar(300) NOT NULL,
  CONSTRAINT PK_mm_modules_files PRIMARY KEY (id)
)");
        }

        $res = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'mm_tmp_users';");
        //Если нет таблицы, создаём ее
        if (!$rows = $res->fetchAll()) {
            $db->query("CREATE TABLE mm_tmp_users (
  [login]    varchar(10) NOT NULL,
  pas        varchar(10),
  email      varchar(50),
  code       varchar(50),
  time       int NOT NULL,
  memb_name  nvarchar(50)
)");
        }

        $res = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME = 'MEMB_INFO';");
        if ($rows = $res->fetchAll()) {
            //Если нет поля mm_money, создаём его
            if (array_search(Array('COLUMN_NAME' => 'mm_money'), $rows) === false) {
                $db->query("ALTER TABLE MEMB_INFO ADD mm_money bigint DEFAULT (0) NOT NULL;");
            }

            //Если нет поля mm_money, создаём его
            if (array_search(Array('COLUMN_NAME' => 'mm_group'), $rows) === false) {
                $db->query("ALTER TABLE MEMB_INFO ADD mm_group varchar(50) DEFAULT 'user'");
            }
            //Устанавливаем всем статус Пользователя
            $db->query("UPDATE MEMB_INFO SET mm_group='user';");

            //Если нет поля mm_reg_time, создаём его
            if (array_search(Array('COLUMN_NAME' => 'mm_reg_time'), $rows) === false) {
                $db->query("ALTER TABLE MEMB_INFO ADD mm_reg_time int");
            }
        }

        //Добавляем колонку reset в Character если её нет
        $res = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME = 'Character' AND COLUMN_NAME='reset';");
        if (!$rows = $res->fetchAll()) {
            $db->query("ALTER TABLE MEMB_INFO ADD reset int DEFAULT (0);");
        }

        header('location:index.php?s=2');
        exit;
    }
} elseif ($step == 2) {

    //Пробуем подключиться к бд
    include_once D . '/sys/config.php';
    include_once D . '/classes/DebugPDO.php';
    try {
        $options = Array("CharacterSet" => "UTF-8");
        $db = new DebugPDO("odbc:Driver=" . DB_DRIVER . ";Server=" . DB_SERVER . ";Database=" . DB_NAME . ";", DB_USER, DB_PASSWORD, $options);
        $db->setAttribute(PDO :: ATTR_DEFAULT_FETCH_MODE, PDO :: FETCH_ASSOC);
    } catch (Exception $e) {
        echo 'Ошибка соединения с базой данных: ' . $e->getMessage();
        exit;
    }

    if (isset($_POST['login'])) {
        $res = $db->prepare("SELECT 1 FROM MEMB_INFO WHERE memb___id=?;");
        $res->execute(Array($_POST['login']));
        if (!$row = $res->fetch()) {
            echo 'Пользователь с таким логином не найден';
            echo '<p><a href="?s=' . $step . '">Назад</a></p>';
            include '_foot.tpl';
            exit;
        }
        
        //Устанавливаем права супер админа
        $res = $db->prepare("UPDATE MEMB_INFO SET mm_group='root' WHERE memb___id=?;");
        $res->execute(Array($_POST['login']));
        echo 'Установка завершена. Проверьте работоспособность сайта, и обязательно удалите папку <b>install</b>!!!';
        echo '<p><a href="/">На главную</a></p>';
        include '_foot.tpl';
        exit;
    }
}

include '_head.tpl';

if (is_file('step' . $step . '.tpl')) {
    include 'step' . $step . '.tpl';
}
?>





<?php

include '_foot.tpl';
?>