<?php
session_start();
require_once "../modelo/usuario.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST["usuario"] ?? '';
    $contrasena = $_POST["contrasena"] ?? '';

    // Llamamos al método de login de la clase Usuario
    $resultado = Usuario::login($usuario, $contrasena);

    if ($resultado && $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $hashGuardado = $fila['contrasena'];

        $loginCorrecto = false;

        // Caso 1: contraseña almacenada como hash
        if (password_verify($contrasena, $hashGuardado)) {
            $loginCorrecto = true;
        }
        // Caso 2: contraseña almacenada en texto plano
        elseif ($contrasena === $hashGuardado) {
            $loginCorrecto = true;

            /* Migramos automáticamente a hash
            $nuevoHash = password_hash($contrasena, PASSWORD_DEFAULT);
            Usuario::actualizarPassword($fila['id'], $nuevoHash);*/
        }

        if ($loginCorrecto) {
            $_SESSION['usuario'] = $fila['nombre'];   
            $_SESSION['usuario_id'] = $fila['id'];
            $_SESSION['rol'] = $fila['rol'];

            header("Location: ../vista/panel.php");
            exit();
        } else {
            header("Location: ../vista/login.php?error=1");
            exit();
        }
    } else {
        header("Location: ../vista/login.php?error=1");
        exit();
    }
}
?>
