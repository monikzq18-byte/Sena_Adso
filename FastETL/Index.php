<?php
session_start();

if (isset($_SESSION['usuario'])) {
    // Si ya hay sesión, lo mando al panel
    header("Location: vista/panel.php");
    exit();
} else {
    // Si no hay sesión, lo mando al login
    header("Location: vista/login.php");
    exit();
}
