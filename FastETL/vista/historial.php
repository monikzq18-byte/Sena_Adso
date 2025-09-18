<?php
session_start();
require_once "../modelo/conexion.php";

$usuario_id = $_SESSION['usuario_id']; // quien está logueado

$resultado = $conexion->query("SELECT * FROM transformaciones WHERE usuario_id = '$usuario_id' ORDER BY fecha DESC");

// 👉 Logo
echo "<div style='text-align:center; margin-bottom:20px;'>
        <img src='../img/Logo.png' alt='Logo FastETL' style='max-height:100px;'>
      </div>";

echo "<h2 style='text-align:center;'>Historial de tus transformaciones</h2>";

// 👉 Contenedor centrado
echo "<div style='display:flex; justify-content:center; margin-top:20px;'>";
echo "<table border='1' cellpadding='8' style='border-collapse:collapse; text-align:center;'>";
echo "<tr style='background:#2a836f; color:white;'>
        <th>ID</th>
        <th>Archivo Origen</th>
        <th>Archivo Transformado</th>
        <th>Formato</th>
        <th>Primary Key</th>
        <th>Fecha</th>
      </tr>";

while ($fila = $resultado->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$fila['id']}</td>";
    echo "<td>{$fila['archivo_origen']}</td>";
    echo "<td><a href='{$fila['archivo_salida']}' download>📥 Descargar</a></td>";
    echo "<td>{$fila['formato_salida']}</td>";
    echo "<td>{$fila['primary_key_columna']}</td>";
    echo "<td>{$fila['fecha']}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 👉 Botón para volver al panel
echo "<div style='text-align:center; margin-top:20px;'>
        <a href='panel.php'><button>⬅ Volver al Panel</button></a>
      </div>";
?>  
