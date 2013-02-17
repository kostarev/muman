<?php

Class Captcha {

    public function __construct() {
        require(D . '/open/kcaptcha/kcaptcha.php');
    }

    public function url() {
        return H . '/open/kcaptcha/?' . session_name() . '=' . session_id();
    }

    public function is_access($keystring) {
        $return = (isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] === $keystring);
        unset($_SESSION['captcha_keystring']);
        return $return;
    }

}

?>
