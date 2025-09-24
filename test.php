<?php
$image = new Imagick('public/images/the-newspaper.png');
$watermark = new Imagick();
$logo      = new Imagick('public/images/contest-info.png');
$text = 'AIB/Mason County Historical Society';
$draw = new ImagickDraw();
$watermark->newImage(350, 80, new ImagickPixel('none'));
$draw->setFont('/mnt/storage/StorageOne/stparch/virtual_sites/aib_historicals/2018/MONOFONT.ttf');
$draw->setFillColor('grey');
$draw->setFillOpacity(1);
$draw->setGravity(Imagick::GRAVITY_NORTHWEST);
//$watermark->annotateImage($draw, 10, 10, 0, $text);
$draw->setGravity(Imagick::GRAVITY_SOUTHEAST);
$draw->setFontSize(20);
$watermark->annotateImage($draw, 5, 15, 0, $text);
$watermark->compositeImage($logo);
for ($w = 0; $w < $image->getImageWidth(); $w += 330) {
    for ($h = 0; $h < $image->getImageHeight(); $h += 120) {
        $image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $w, $h);
    }
}
$image->setImageFormat('png');
header('Content-type: image/png');
echo $image;
?>