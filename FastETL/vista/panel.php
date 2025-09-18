<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FastETL - Panel</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    
    <div class="contenedor">
        <div class="logo-area">
            <img src="../img/logo.png" alt="sLogo FastETL">
        </div>
        <h2>Subir archivo para transformación</h2>

        <form action="../controlador/procesar_archivo.php" method="post" enctype="multipart/form-data">
            <label for="archivo">Seleccionar archivo <br> (CSV, XLSX, JSON, XML, TXT): </label> <br>
            <input type="file" name="archivo" accept=".csv,.xlsx,.json,.xml,.txt" required>

            <label for="formato">Transformar a:</label>
            <select name="formato" required>
                <option value="csv">CSV</option>
                <option value="xlsx">Excel (XLSX)</option>
                <option value="json">JSON</option>
                <option value="xml">XML</option>
                <option value="sql">SQL</option>
            </select>

            <br><br>
            <input type="submit" value="Subir y Analizar">
        </form>

        <br>

        <div class ="botones">
        <a href="historial.php"><button>Ver Historial</button></a>
        <a href="../vista/logout.php"><button>Cerrar Sesión</button></a>
        </div>
        <br><br>
    </div>
</body>
</html>
