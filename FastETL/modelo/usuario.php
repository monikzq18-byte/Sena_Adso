<?php
class Usuario {
    public static function login($cedula, $contrasena) {
        $conn = new mysqli("localhost", "root", "", "fastetl");

        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        // Buscar el usuario por cédula
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE cedula = ? LIMIT 1");
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $stmt->close();
        $conn->close();

        return $resultado;
    }

    public static function actualizarPassword($id, $nuevoHash) {
        $conn = new mysqli("localhost", "root", "", "fastetl");

        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("UPDATE usuarios SET contrasena=? WHERE id=?");
        $stmt->bind_param("si", $nuevoHash, $id);
        $stmt->execute();

        $stmt->close();
        $conn->close();
    }
}
?>
