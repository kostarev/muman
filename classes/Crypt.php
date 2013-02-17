<?php
Class Crypt {

    //PHP функция для обратимого шифрования
    static function encode($String, $Password) {
        $Salt = $_SERVER['HTTP_HOST'] . 'DJsda58sFEv';
        $StrLen = strlen($String);
        $Seq = $Password;
        $Gamma = '';
        while (strlen($Gamma) < $StrLen) {
            $Seq = pack("H*", sha1($Gamma . $Seq . $Salt));
            $Gamma.=substr($Seq, 0, 8);
        }

        return $String ^ $Gamma;
    }

    static function decode($String, $Password) {
        return Crypt::encode($String, $Password);
    }

}

?>
