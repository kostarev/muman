<?php

if (isset($_GET['img'])) {
    $hex = $_GET['img'];
}

//Размер пикселя изображения (в пикселях монитора)
if (isset($_GET['pix'])) {
    $pixelSize = abs((int)$_GET['pix']);
}else{
    $pixelSize = 1;
}

if($pixelSize > 10){
    $pixelSize = 10;
}

//Логотип по умолчанию
if (preg_match('/[^a-zA-Z0-9]/', $hex) || $hex == '') {
    $hex = '0044450004445550441551554515515655555566551551660551166000566600';
}

$img = imagecreatetruecolor(8*$pixelSize, 8*$pixelSize);

$colors = Array('cccccc','000000','8c8a8d','ffffff','fe0000','ff8a00','ffff00','8cff01','00ff00','01ff8d','00ffff','008aff','0000fe','8c00ff','ff00fe','ff008c');

//Код прозрачного цвета
$transp = imagecolorallocate($img, 204,204,204);
imagecolortransparent ($img, $transp);

for ($y = 0; $y < 8; $y++) {
    for ($x = 0; $x < 8; $x++) {
        $offset = ($y * 8) + $x;
        $color_id = hexdec(substr($hex, $offset, 1));
        $c = $colors[$color_id];
        
        $r = '0x'.substr($c,0,2);
        $g = '0x'.substr($c,2,2);
        $b = '0x'.substr($c,4,2);

        $row[$x] = $x * $pixelSize;
        $row[$y] = $y * $pixelSize;
        $row2[$x] = $row[$x] + $pixelSize;
        $row2[$y] = $row[$y] + $pixelSize;
        $color[$y][$x] = imagecolorallocate($img, $r, $g, $b);
        imagefilledrectangle($img, $row[$x], $row[$y], $row2[$x], $row2[$y], $color[$y][$x]);
    }
}

Header('Content-type: image/png');
Imagepng($img);
?>