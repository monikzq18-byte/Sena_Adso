<?php
require_once "../modelo/conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = $_POST["cedula"] ?? '';
    $nombre = $_POST["nombre"] ?? '';
    $correo = $_POST["correo"] ?? '';
    $contrasena = $_POST["contrasena"] ?? '';
    $confirmar = $_POST["confirmar"] ?? '';
    $rol = $_POST["rol"] ?? '';

    // Validaciones
    if (empty($cedula) || empty($nombre) || empty($correo) || empty($contrasena) || empty($confirmar)) {
        header("Location: ../vista/registro.php?error=Todos los campos son obligatorios");
        exit();
    }

    if ($contrasena !== $confirmar) {
        header("Location: ../vista/registro.php?error=Las contraseñas no coinciden");
        exit();
    }

       // Validar si ya existe el correo
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Redirigir con mensaje
        header("Location: ../vista/registro.php?existe=1&correo=$correo");
        exit();
    }


    // Guardar en la BD
    $stmt = $conexion->prepare("INSERT INTO usuarios (cedula, nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $cedula, $nombre, $correo, $contrasena, $rol);

    if ($stmt->execute()) {
        header("Location: ../vista/registro.php?ok=Usuario registrado correctamente");
    } else {
        header("Location: ../vista/registro.php?error=Error en el registro");
    }
    exit();
}
?>
