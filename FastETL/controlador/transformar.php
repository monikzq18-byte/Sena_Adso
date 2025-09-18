<?php
session_start();
require_once "../modelo/conexion.php";

// PhpSpreadsheet (para Excel)
require "../vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$archivo = $_POST["archivo"];
$formato = strtolower($_POST["formato"]); //  minúsculas
$pk = $_POST["pk"];
$usuario_id = $_SESSION["usuario_id"];

// Generar nombre base
$nombreBase = "../uploads/transformado_" . time();

// Detectar extensión de entrada
$extensionEntrada = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));

// Leer datos en formato tabular
$datos = [];

// === CSV ===
if ($extensionEntrada === "csv") {
    if (($handle = fopen($archivo, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $datos[] = $row;
        }
        fclose($handle);
    }
}

// === JSON ===
elseif ($extensionEntrada === "json") {
    $jsonData = json_decode(file_get_contents($archivo), true);
    if (isset($jsonData[0]) && is_array($jsonData[0])) {
        $headers = array_keys($jsonData[0]);
        $datos[] = $headers;
        foreach ($jsonData as $row) {
            $datos[] = array_values($row);
        }
    }
}

// === Excel ===
elseif (in_array($extensionEntrada, ["xlsx", "xls"])) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo);
    $hoja = $spreadsheet->getActiveSheet();
    $datos = $hoja->toArray();
}

// === XML ===
elseif ($extensionEntrada === "xml") {
    $xml = simplexml_load_file($archivo);
    $headers = [];
    $rows = [];
    foreach ($xml->row as $row) {
        $fila = [];
        foreach ($row->children() as $col => $val) {
            if (!in_array($col, $headers)) $headers[] = $col;
            $fila[$col] = (string)$val;
        }
        $rows[] = $fila;
    }
    $datos[] = $headers;
    foreach ($rows as $r) {
        $fila = [];
        foreach ($headers as $h) {
            $fila[] = $r[$h] ?? "";
        }
        $datos[] = $fila;
    }
}

// función para limpiar nombres
function limpiarNombreXML($col) {
    $col = strtolower(trim($col));
    $col = preg_replace('/[^a-z0-9_]/', '_', $col);
    $col = preg_replace('/_+/', '_', $col);
    if (preg_match('/^[0-9]/', $col)) {
        $col = "col_" . $col;
    }
    return $col ?: "columna";
}

// === Transformación al formato de salida ===
switch ($formato) {
    case "csv":
        $nombreSalida = $nombreBase . ".csv";
        $fp = fopen($nombreSalida, "w");
        foreach ($datos as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        break;

    case "json":
        $nombreSalida = $nombreBase . ".json";
        $headers = $datos[0];
        $jsonData = [];
        for ($i = 1; $i < count($datos); $i++) {
            $filaAsociativa = [];
            foreach ($headers as $j => $colName) {
                $filaAsociativa[$colName] = $datos[$i][$j] ?? null;
            }
            $jsonData[] = $filaAsociativa;
        }
        file_put_contents($nombreSalida, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        break;

    case "xml":
        $nombreSalida = $nombreBase . ".xml";
        $xml = new SimpleXMLElement("<root/>");
        $headers = $datos[0];
        $headers = array_map('limpiarNombreXML', $headers);
        for ($i = 1; $i < count($datos); $i++) {
            $item = $xml->addChild("row");
            foreach ($headers as $j => $colName) {
                $valor = isset($datos[$i][$j]) ? $datos[$i][$j] : "";
                $item->addChild($colName, htmlspecialchars($valor));
            }
        }
        $xml->asXML($nombreSalida);
        break;

    case "xlsx":
    case "xls":
    case "excel":
        $nombreSalida = $nombreBase . ".xlsx";
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($datos as $i => $row) {
            foreach ($row as $j => $cell) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($j + 1);
                $sheet->setCellValue($col . ($i + 1), $cell);
            }
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($nombreSalida);
        break;

    case "sql":
        
        // Archivo 1: CREATE TABLE con detección de tipos
        $nombreCreate = $nombreBase . "_create.sql";
        $headers = $datos[0];
        $createSQL = "CREATE TABLE tabla_destino (\n";

        // recorrer columnas
        foreach ($headers as $j => $col) {
            $colLimpio = limpiarNombreXML($col);
            $tipo = "VARCHAR(255)"; // por defecto

            $isInt = true;
            $isDecimal = true;
            $isDate = true;
            $isDateTime = true;
            $maxLen = 0;

            // recorrer filas para detectar tipo
            for ($i = 1; $i < count($datos); $i++) {
                $valor = trim($datos[$i][$j] ?? "");
                if ($valor === "") continue;

                $maxLen = max($maxLen, strlen($valor));

                // validar INT
                if (!preg_match('/^-?\d+$/', $valor)) {
                    $isInt = false;
                }

                // validar DECIMAL
                if (!preg_match('/^-?\d+(\.\d+)?$/', $valor)) {
                    $isDecimal = false;
                }

                // validar DATE
                if (!(preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) || preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valor))) {
                    $isDate = false;
                }

                // validar DATETIME
                if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $valor)) {
                    $isDateTime = false;
                }
            }

            // decidir tipo final
            if ($isInt) {
                $tipo = "INT";
            } elseif ($isDecimal) {
                $tipo = "DECIMAL(15,2)";
            } elseif ($isDate) {
                $tipo = "DATE";
            } elseif ($isDateTime) {
                $tipo = "DATETIME";
            } else {
                $len = ($maxLen > 0) ? $maxLen : 255;
                $tipo = "VARCHAR(" . min($len, 500) . ")"; // tope de 500
            }

            $createSQL .= "    $colLimpio $tipo,\n";
        }

        $createSQL = rtrim($createSQL, ",\n") . "\n);\n";
        file_put_contents($nombreCreate, $createSQL);


        // Archivo 2: INSERTS
        $nombreInsert = $nombreBase . "_inserts.sql";
        $fp = fopen($nombreInsert, "w");
        for ($i = 1; $i < count($datos); $i++) {
            $row = array_map(function($v) use ($conexion) {
                return "'" . $conexion->real_escape_string($v) . "'";
            }, $datos[$i]);

            $sql = "INSERT INTO tabla_destino (" . implode(",", $headers) . ") VALUES (" . implode(",", $row) . ");\n";
            fwrite($fp, $sql);
        }
        fclose($fp);

        // Guardamos ambos nombres de salida en array
        $nombreSalida = [$nombreCreate, $nombreInsert];
        break;

    default:
        die("❌ Formato de salida no soportado: $formato");
}

// Guardar en la BD
if (is_array($nombreSalida)) {
    foreach ($nombreSalida as $ns) {
        $conexion->query("INSERT INTO transformaciones (usuario_id, archivo_origen, archivo_salida, formato_salida, primary_key_columna, fecha) 
                          VALUES ('$usuario_id', '$archivo', '$ns', '$formato', '$pk', NOW())");
    }
} else {
    $conexion->query("INSERT INTO transformaciones (usuario_id, archivo_origen, archivo_salida, formato_salida, primary_key_columna, fecha) 
                      VALUES ('$usuario_id', '$archivo', '$nombreSalida', '$formato', '$pk', NOW())");
}

// Mensajes y previsualización
// 👉 Logo
echo "<div style='text-align:center; margin-bottom:20px;'>
        <img src='../img/Logo.png' alt='Logo FastETL' style='max-height:100px;'>
      </div>";
echo "<h2 style='text-align:center; color:#2c3e50;'>Archivo transformado correctamente</h2>";
echo "<p>Archivo original: $archivo</p>";
echo "<p>Formato destino: $formato</p>";
echo "<p>Primary Key: $pk</p>";

echo "<h3 style='text-align:center; color:#2c3e50;'>Previsualización de datos</h3>";
echo "<table border='1' cellpadding='5'>";
foreach (array_slice($datos, 0, 5) as $row) {
    echo "<tr>";
    foreach ($row as $cell) {
        echo "<td>" . htmlspecialchars($cell) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

// ✅ Botones de descarga
if (is_array($nombreSalida)) {
    foreach ($nombreSalida as $ns) {
        $nombre_descarga = basename($ns);
        echo "<br><a href='$ns' download='$nombre_descarga'>
                <button>📥 Descargar $nombre_descarga</button>
              </a>";
    }
} else {
    $nombre_descarga = basename($nombreSalida);
    echo "<br><a href='$nombreSalida' download='$nombre_descarga'>
            <button>📥 Descargar archivo en $formato</button>
          </a>";
}


// 👉 Botón para volver al panel
echo "<div style='text-align:center; margin-top:20px;'>
        <a href='panel.php'><button>⬅ Volver al Panel</button></a>
        
        <a href='../vista/historial.php'><button>Ver Historial</button></a>";
        // Link al historial
        
    echo "<br><br>

      </div>";
?>
