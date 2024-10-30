<?php
$image_name = $_GET['image'];
$image = imagecreatefromjpeg($image_name);
if (!$image) {
	exit;
}
$watermark_file = $_GET['watermark'];
if ($_GET['data'] == substr(dirname($image_name), 0, strlen($_GET['data']))) {
	ldw_exit($image);
}
$sizes = explode(":", $_GET['sizes']);
for ($i = 0; $i < count($sizes); $i++) {
	if ($sizes[$i] == "original") {
		if (preg_match("/[0-9]x[1-9]+.(gif|jpe?g|png)$/i", $image_name) != 1) {
			$size_match = true;
			break;
		}
	} else {
		$htaccess_width = substr($sizes[$i], 0, stripos($sizes[$i], 'x'));
		$htaccess_height = substr($sizes[$i], stripos($sizes[$i], 'x') + 1, strlen($sizes[$i]));
		if (imagesx($image) == $htaccess_width || imagesy($image) == $htaccess_height) {
			$size_match = true;
			break;
		}
	}
}
if ($size_match == true) {
	$watermark_orig = imagecreatefrompng($watermark_file);
	if (!$watermark_orig) {
		ldw_exit($image);
	}
	$width_orig = imagesx($watermark_orig);
	$height_orig = imagesy($watermark_orig);
	$watermark_ratio = $width_orig / $height_orig;
	$width_new = intval((imagesx($image) * 25) / 100);
	if ($width_new < 1) {
		$width_new = 1;
	}
	$height_new = intval((imagesy($image) * 25) / 100);
	if ($height_new < 1) {
		$height_new = 1;
	}
	$width_new_ratio = $width_new / $width_orig;
	$height_new_temp = 	$height_orig * $width_new_ratio;
	if ($height_new_temp > $height_new) {
		$width_new = $height_new * $watermark_ratio;
	} else {
		$height_new = $width_new / $watermark_ratio;
	}
	if ($width_new < 1) {
		$width_new = 1;
	}
	if ($height_new < 1) {
		$height_new = 1;
	}
	$watermark = imagecreatetruecolor($width_new, $height_new);
	imagealphablending($watermark, false);
	imagecopyresampled($watermark, $watermark_orig, 0, 0, 0, 0, $width_new, $height_new, $width_orig, $height_orig);
	$watermark_pos_x = intval((imagesx($image) / 2) - (imagesx($watermark) / 2));
	$watermark_pos_y = imagesy($image) - imagesy($watermark) - 25;
	imagecopy($image, $watermark, $watermark_pos_x, $watermark_pos_y, 0, 0, imagesx($watermark), imagesy($watermark));
}
ldw_exit($image);
function ldw_exit($image) {
	header('Content-Type: image/jpeg');
	imagejpeg($image, NULL, 100); // quality is optional, and ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file). The default is the default IJG quality value (about 75)
	imagedestroy($image);
	if ($watermark_orig) {
		imagedestroy($watermark_orig);
		imagedestroy($watermark);
	}
}
?>
