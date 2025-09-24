<?php
include('config/aib.php');

if(isset($_REQUEST['original_file'])){
	$file_name = $_REQUEST['original_file'];
	$Buffer = new Imagick(AIB_AD_STORAGE_PATH.'/'.$file_name);
	header('Content-Type: image/' . strtolower($Buffer->getImageFormat()));
	echo $Buffer->getimageblob();
}
exit; 