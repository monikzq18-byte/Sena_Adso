<?php
$conexion = new mysqli("localhost", "root", "", "fastetl");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
