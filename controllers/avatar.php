<?php

class Controller_avatar extends Controller_Base {

    private $avatars_dir, $ava_sizes;

    function __construct($args) {
        parent::__construct($args);
        $this->avatars_dir = 'data/avatars';
        $this->ava_sizes = Array('original' => 0, 'small' => 50, 'norm' => 100, 'big' => 200);
    }

    function index() {

        $id = isset($this->args[0]) ? (int) $this->args[0] : 0;
        $size = (isset($this->args[1]) AND isset($this->ava_sizes[$this->args[1]])) ? $this->ava_sizes[$this->args[1]] : 0;

        $dir = $this->avatars_dir . '/' . $size;
        $file = $dir . '/' . $id;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        if (!is_file($file)) {
            $file = $dir . '/0';
            if (!is_file($file)) {
                $src = $this->avatars_dir . '/avatar.jpg';
                if ($size) {
                    Images::resize($src, $file, $size);
                } else {
                    copy($src, $file);
                }
            }
        }

        header('Content-type: image/jpg');
        echo file_get_contents($file);
    }

}

?>
