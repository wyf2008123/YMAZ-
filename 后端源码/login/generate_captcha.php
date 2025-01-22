<?php
session_start(); // 开启 session

// 生成随机验证码
$random_code = substr(md5(rand()), 0, 5); // 随机生成5位验证码
$_SESSION["captcha"] = strtolower($random_code); // 将验证码存储到 session，并转换为小写字母

// 创建画布
$width = 100;
$height = 40;
$image = imagecreatetruecolor($width, $height);

// 设置背景色和文本色
$background_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);

// 填充背景色
imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

// 在画布上绘制验证码
$font = 'font.ttf'; // 替换为实际的字体文件路径
imagettftext($image, 20, 0, 10, 30, $text_color, $font, $random_code);

// 输出图像
header('Content-Type: image/png');
imagepng($image);

// 释放资源
imagedestroy($image);
?>
