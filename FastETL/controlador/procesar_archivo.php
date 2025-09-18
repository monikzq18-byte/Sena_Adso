<?php
session_start();
require_once "../modelo/conexion.php";
require "../vendor/autoload.php"; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_FILES["archivo"]["error"] === UPLOAD_ERR_OK) {
    $nombreArchivo = $_FILES["archivo"]["name"];
    $rutaTemp = $_FILES["archivo"]["tmp_name"];
    $formato = $_POST["formato"];

    // Guardar en carpeta uploads
    $rutaDestino = "../uploads/" . basename($nombreArchivo);
    move_uploaded_file($rutaTemp, $rutaDestino);

    // Insertar en BD tabla uploads
    $conexion->query("INSERT INTO uploads (usuario_id, nombre_archivo, formato_origen, formato_destino, fecha_subida) 
                      VALUES ('{$_SESSION['usuario_id']}', '$nombreArchivo', '".pathinfo($nombreArchivo, PATHINFO_EXTENSION)."', '$formato', NOW())");

    // Detectar extensión
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
   $columnas = [];

// CSV
        if ($extension === "csv") {
            if (($handle = fopen($rutaDestino, "r")) !== FALSE) {
                $columnas = fgetcsv($handle, 1000, ",");
                fclose($handle);
            }
        }
        // Excel (requiere PhpSpreadsheet)
        elseif ($extension === "xlsx") {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($rutaDestino);
            $hoja = $spreadsheet->getActiveSheet();
            $columnas = $hoja->rangeToArray('A1:' . $hoja->getHighestColumn() . '1')[0];
        }
        // JSON
        elseif ($extension === "json") {
            $jsonData = json_decode(file_get_contents($rutaDestino), true);
        if (isset($jsonData[0]) && is_array($jsonData[0])) {
            $columnas = array_keys($jsonData[0]);
        }
    }

    // Mostrar formulario para seleccionar PK
    // 👉 Logo
    echo "<div style='text-align:center; margin-bottom:20px;'>
            <img src='../img/Logo.png' alt='Logo FastETL' style='max-height:100px;'>
        </div>";

  // 👉 Formulario centrado
    echo "<div style='text-align:center;'>";
    echo "<form action='transformar.php' method='post' style='display:inline-block; text-align:center; justify-content: center; padding:20px; border:1px solid #ccc; border-radius:10px; background:#f9f9f9;'>";
    echo "<input type='hidden' name='archivo' value='$rutaDestino'>";
    echo "<input type='hidden' name='formato' value='$formato'>";

    echo "<label for='pk' style='color:#2c3e50; font-weight:bold;'>Primary Key:</label><br>";
    echo "<select name='pk' required style='margin:10px 0; padding:5px; width:100%;'>";
    foreach ($columnas as $col) {
    echo "<option value='" . htmlspecialchars($col) . "'>" . htmlspecialchars($col) . "</option>";
    }
    echo "</select><br><br>";

    echo "<input type='submit' value='Transformar' 
            style='background:#2c3e50; color:white; border:none; padding:10px 20px; cursor:pointer; border-radius:5px;justify-content: center;'>";
    echo "</form>";
    echo "</div>";

    } else {
        echo "Error al subir archivo.";
}
?>
