<?php
function getUserByUsername($username) {
    global $pdo; // Asumiendo PDO para BD
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function saveRememberToken($userId, $token) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE usuarios SET remember_token = :token WHERE id = :id");
    $stmt->execute(['token' => $token, 'id' => $userId]);
}

function generarCaptchaTexto($length = 6) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // sin confusión
    $text = '';
    for ($i = 0; $i < $length; $i++) {
        $text .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $text;
}

function generarCaptchaImagen($text) {
    // Genera imagen captcha con GD y devuelve base64
    $img = imagecreatetruecolor(100, 40);
    $bg = imagecolorallocate($img, 255, 255, 255);
    $textcolor = imagecolorallocate($img, 0, 0, 0);
    imagefilledrectangle($img, 0, 0, 100, 40, $bg);
    imagestring($img, 5, 15, 10, $text, $textcolor);
    ob_start();
    imagepng($img);
    $img_data = ob_get_contents();
    ob_end_clean();
    imagedestroy($img);
    return 'data:image/png;base64,' . base64_encode($img_data);
}
?>
