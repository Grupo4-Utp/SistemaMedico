<?php
session_start();

$width = 120;
$height = 40;

$captcha_text = $_SESSION['captcha_text'] ?? 'ERROR';

$image = imagecreatetruecolor($width, $height);

$bg_color = imagecolorallocate($image, 255, 255, 255); // blanco
$text_color = imagecolorallocate($image, 0, 0, 0); // negro
$noise_color = imagecolorallocate($image, 100, 120, 180);

imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width - 1), rand(0, $height - 1), $noise_color);
}

for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width - 1), rand(0, $height - 1), rand(0, $width - 1), rand(0, $height - 1), $noise_color);
}

$font_path = __DIR__ . '/fonts/arial.ttf';

if (file_exists($font_path)) {
    $font_size = 30;
    $x = 10;
    for ($i = 0; $i < strlen($captcha_text); $i++) {
        $angle = rand(-15, 15);
        $y = rand(25, 35);
        $char = $captcha_text[$i];
        imagettftext($image, $font_size, $angle, $x, $y, $text_color, $font_path, $char);
        $x += 18;
    }
} else {
    
    imagestring($image, 5, 15, 10, $captcha_text, $text_color);
}

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
exit;
