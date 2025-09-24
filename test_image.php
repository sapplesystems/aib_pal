<?php
$image = new Imagick();
$image->readImage("public/images/the-newspaper.png");
$watermark = new Imagick();
$watermark->readImage("public/images/contest-info.png");

//Watermark text start
$watermarkText = new Imagick();
$text = '/Mason County Historical Society';
$draw = new ImagickDraw();
$watermarkText->newImage(350, 80, new ImagickPixel('none'));
$draw->setFont('/mnt/storage/StorageOne/stparch/virtual_sites/aib_historicals/2018/MONOFONT.ttf');
$draw->setFillColor('grey');
$draw->setFillOpacity(1);
$draw->setGravity(Imagick::GRAVITY_NORTHWEST);
$draw->setGravity(Imagick::GRAVITY_SOUTHEAST);
$draw->setFontSize(20);
$watermarkText->annotateImage($draw, 5, 15, 0, $text);
//Watermark text end

for ($w = 0; $w < $image->getImageWidth(); $w += 330) {
    for ($h = 0; $h < $image->getImageHeight(); $h += 120) {
        $image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $w, $h);
        $image->compositeImage($watermarkText, Imagick::COMPOSITE_OVER, $w-56, $h-40);
    }
}
header("Content-Type: image/" . $image->getImageFormat());
echo $image;
