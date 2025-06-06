<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

function login($usuario, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM Acceso_Movil WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['contrasena'])) {
        // Guardamos datos en sesión con claves consistentes y en minúsculas
        $_SESSION['user'] = [
            'id' => $user['ID_Usuario'],
            'usuario' => $user['usuario'],
            'tipo' => $user['tipo_usuario'],
            'id_paciente' => $user['ID_Paciente'],
            'id_medico' => $user['ID_Medico']
        ];
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function logout() {
    $_SESSION = [];

    if (session_status() == PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}
