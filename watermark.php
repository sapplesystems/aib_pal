<?php
require_once 'config/config.php';
ini_set('display_errors',1);
if (isset($_FILES['image']['name'])) {
    list($txt, $ext) = explode(".", $_FILES['image']['name']);
    $file_name = "images/" . rand(0, 9999) . '.' . $ext;
    $upload = copy($_FILES['image']['tmp_name'], $file_name);
    if ($upload == true) {
        $watermark = $_POST['watermark_text'];
        addTextWatermark($file_name, $watermark, $file_name);
        echo '<br><img style="text-align:center;" src="' . $file_name . '" class="preview" ><br><br><br>';
    } else {
        echo 'Error uploading image';
    }
}

function addTextWatermark($src, $watermark, $save = NULL) {
    list($width, $height) = getimagesize($src);
    $image_color = imagecreatetruecolor($width, $height);
    $image = imagecreatefromjpeg($src);
    imagecopyresampled($image_color, $image, 0, 0, 0, 0, $width, $height, $width, $height);
    $txtcolor = imagecolorallocate($image_color, 255, 255, 255);
    
    $font_size = 20;
    imagettftext($image_color, $font_size, 0, 50, 150, $txtcolor, FONT_PATH, $watermark);
    if ($save <> '') {
        imagejpeg($image_color, $save, 100);
    } else {
        header('Content-Type: image/jpeg');
        imagejpeg($image_color, null, 100);
    }
    imagedestroy($image);
    imagedestroy($image_color);
}
?>
<form action="" method="post" enctype="multipart/form-data">
    <table align="center" style="width:50%; margin:5% 25%; border:solid 1px #ccc; padding: 20px;">
        <tr>
            <th colspan="2"><h2>Add Watermark To Images</h2></th>
        </tr>
        <tr>
            <td>Select Image:</td>
            <td><input type="file" name="image" value=""><br><span style="color:red;">For testing upload only jpg,jpeg image</span></td>
        </tr>
        <tr>
            <td>Enter Watermark Text:</td>
            <td><input type="text" name="watermark_text" id="watermark_text" value=""></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input type="submit" value="Upload"></td>
        </tr>
    </table>
</form>