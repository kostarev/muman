<?php

class Func {

    //Валидация логина
    static function valid_login($login) {
        //$login = Func::translit($login);
        if (preg_match("([^a-zA-Z0-9])", $login)) {
            return false;
        }
        return true;
    }

    //Валидация имени
    static function valid_name($str) {
        // $str = Func::translit($str);
        if (preg_match("([^a-zA-Z0-9])", $str)) {
            return false;
        }
        return true;
    }

    static function valid_email($str) {
        return filter_var($str, FILTER_VALIDATE_EMAIL);
    }

    //Транслит
    static function translit($string) {
        $table = array(
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'YO',
            'Ж' => 'ZH',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'J',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'C',
            'Ч' => 'CH',
            'Ш' => 'SH',
            'Щ' => 'CSH',
            'Ь' => '',
            'Ы' => 'Y',
            'Ъ' => '',
            'Э' => 'E',
            'Ю' => 'YU',
            'Я' => 'YA',
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'yo',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'csh',
            'ь' => '',
            'ы' => 'y',
            'ъ' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
        );

        $output = str_replace(
                array_keys($table), array_values($table), $string
        );

        return $output;
    }

    /*
      $kolvo - число записей
      $on_page- записей на страницу
      $page - номер текущей страницы

      $arr['page'] -  текущая страница
      $arr['start'] - начальная запись для sql запроса в LIMIT $arr['start'],$on_page
      $arr['max_page'] - максимальная страница
     */

    static function pages_arr($kolvo, $on_page, $page) {
        $max_page = ceil($kolvo / $on_page);
        if ($max_page < 1) {
            $max_page = 1;
        }

        if ($page == 'end') {
            $page = $max_page;
        } else {
            $page = (int) $page;
            if ($page < 1) {
                $page = 1;
            } elseif ($page > $max_page) {
                $page = $max_page;
            }
        }

        $start = ($page - 1) * $on_page;
        $arr = Array();
        $arr['start'] = $start;
        $arr['max_page'] = $max_page;
        $arr['page'] = $page;
        return $arr;
    }

//Формируем html строку страниц
//page - текущая страница
//max - кол-во страниц
//shablon - http://site.ru/big_text.php?p={page}
//size - Количество отображаемых элементов в  строке страниц
//----------------------------------------------
    static function pages($page, $max, $shablon, $size = 5) {
        $page_str = '';
        $page = (int) $page;
        $max = (int) $max;
        $size = (int) $size;

        if ($max < 2) {
            return $page_str;
        }

        if ($page > 1) {
            $page_str.='<a href="' . str_replace('{page}', 1, $shablon) . '"><span>&lt;&lt;</span></a><a href="' . str_replace('{page}', $page - 1, $shablon) . '"><span>&lt;</span></a>';
        }

        for ($i = 1; $i <= $max; $i++) {
            if ($i < $page - $size OR $i > $page + $size) {
                continue;
            }
            if ($page == $i) {
                $page_str.='<span class="courrent">' . $i . '</span>';
            } else {
                $page_str.='<a href="' . str_replace('{page}', $i, $shablon) . '"><span>' . $i . '</span></a>';
            }
        }
        if ($page < $max) {
            $page_str.='<a href="' . str_replace('{page}', $page + 1, $shablon) . '"><span>&gt;</span></a><a href="' . str_replace('{page}', $max, $shablon) . '"><span>&gt;&gt;</span></a>';
        }
        return '<div class="pages">' . $page_str . '</div>';
    }

//----------------------------------------
// Функция генерации случайного набора символов
    static function rand_string($stringLength = 0, $simbols = '') {
        // Из каких символов будет собирать строку
        if ($simbols) {
            $textCharacters = $simbols;
        } else {
            $textCharacters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            //  $textCharacters = "abcdefghijklmnopqrstuvwxyz";
        }
        // Переменная для хранения строки
        $string = '';
        // Выбираем случайную длину от 8 до 12
        if (!$stringLength) {
            $stringLength = mt_rand(8, 12);
        }

        // Составляем строку
        while (strlen($string) < $stringLength) {
            $string .= substr($textCharacters, mt_rand(0, strlen($textCharacters) - 1), 1);
        }
        return $string;
    }

//-----------------------------------


    static function send_mail($name_from, // имя отправителя
            $email_from, // email отправителя
            $name_to, // имя получателя
            $email_to, // email получателя
            $subject, // тема письма
            $body, // текст письма
            $data_charset = 'UTF-8', // кодировка переданных данных
            $send_charset = 'KOI8-R' // кодировка письма                       
    ) {
        $to = Func::mime_header_encode($name_to, $data_charset, $send_charset)
                . ' <' . $email_to . '>';
        $subject = Func::mime_header_encode($subject, $data_charset, $send_charset);
        $from = Func::mime_header_encode($name_from, $data_charset, $send_charset)
                . ' <' . $email_from . '>';
        if ($data_charset != $send_charset) {
            $body = iconv($data_charset, $send_charset, $body);
        }
        $headers = "From: $from\r\n";
        $headers .= "Content-type: text/plain; charset=$send_charset\r\n";

        return mail($to, $subject, $body, $headers);
    }

    static function mime_header_encode($str, $data_charset, $send_charset) {
        if ($data_charset != $send_charset) {
            $str = iconv($data_charset, $send_charset, $str);
        }
        return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
    }

    //Возвращает дату unixTime в читаемом виде
    static function unix2human($time, $template = 'H:i d.m.Y') {
        return date($template, $time);
    }

    //Возвращает колличество секунд в читаемом виде
    static function sec2human($time) {
        return sprintf("%02d ч. %02d мин. %02d сек.", (int) ($time / 3600), (int) (($time % 3600) / 60), $time % 60);
    }

    //Замена ссылок на html код
    static function links($text) {
        $text = preg_replace("?((f|ht){1}t(p|ps)://)[^\s,@,*,^,\,\{,\},\[,\],\(,\),\",\',!]+?i", '<a href="$0">$0</a>', $text);
        return $text;
    }

    //Фильтр html
    static function filtr($text) {
        return htmlspecialchars(trim($text));
    }

    //Скачивание файла
    function download($filename, $name = '', $mimetype = '') {
        if (!file_exists($filename)) {
            throw new Exception('Файл не найден');
        }
        @ob_end_clean();
        $from = 0;
        $size = filesize($filename);
        $to = $size;

        if (!$mimetype) {

            $type = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            $mimes = Array(
                '3gp' => 'video/3gpp', '7z' => 'application/x-7z-compressed', 'avi' => 'video/x-msvideo',
                'bmp' => 'image/x-ms-bmp', 'css' => 'text/css', 'djv' => 'image/vnd.djvu', 'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'exe' => 'application/x-msdos-program', 'flac' => 'audio/flac', 'flv' => 'video/x-flv',
                'gif' => 'image/gif', 'htm' => 'text/plain', 'html' => 'text/plain',
                'ico' => 'image/x-icon', 'info' => 'application/x-info',
                'iso' => 'application/x-iso9660-image', 'jad' => 'text/vnd.sun.j2me.app-descriptor',
                'jar' => 'application/java-archive', 'java' => 'text/x-java', 'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg', 'm3u' => 'audio/x-mpegurl', 'mid' => 'audio/midi',
                'midi' => 'audio/midi', 'mkv' => 'video/x-matroska', 'mp2' => 'audio/mpeg',
                'mp3' => 'audio/mpeg', 'mp4' => 'video/mp4', 'mpeg' => 'video/mpeg',
                'oga' => 'audio/ogg', 'ogg' => 'audio/ogg', 'ogv' => 'audio/ogg', 'ogx' => 'audio/ogg',
                'php' => 'application/x-httpd-php', 'php3' => 'application/x-httpd-php3',
                'php4' => 'application/x-httpd-php4', 'phps' => 'application/x-httpd-php-source',
                'png' => 'image/png', 'rar' => 'application/rar', 'shtml' => 'text/plain',
                'sis' => 'application/vnd.symbian.install', 'sisx' => 'x-epoc/x-sisx-app',
                'swf' => 'application/x-shockwave-flash', 'tar' => 'application/x-tar',
                'taz' => 'application/x-gtar', 'text' => 'text/plain', 'torrent' => 'application/x-bittorrent',
                'txt' => 'text/plain', 'wav' => 'audio/x-wav', 'xhtml' => 'application/xhtml+xml',
                'xls' => 'application/vnd.ms-excel', 'xml' => 'application/xml',
                'zip' => 'application/zip');

            $mimetype = isset($mimes[$type]) ? $mimes[$type] : 'application/octet-stream';
        }

        header('HTTP/1.1 200 Ok');
        $etag = md5($filename);
        header('ETag: "' . $etag . '"');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . ($to - $from));
        header('Connection: close');
        header('Content-Type: ' . $mimetype);
        header('Last-Modified: ' . gmdate('r', filemtime($filename)));
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($filename)) . " GMT");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600) . " GMT");

        if (preg_match('#^image/#i', $mimetype)){
            header('Content-Disposition: filename="' . $name . '";');
        }
        else{
            header('Content-Disposition: attachment; filename="' . $name . '";');
        }


        $f = fopen($filename, 'rb');

        fseek($f, $from, SEEK_SET);
        $size = $to;
        $downloaded = 0;
        while (!feof($f) and !connection_status() and ($downloaded < $size)) {
            $block = min(1024 * 8, $size - $downloaded);
            echo fread($f, $block);
            $downloaded += $block;
            flush();
        }
        fclose($f);
        exit;
    }

}

?>