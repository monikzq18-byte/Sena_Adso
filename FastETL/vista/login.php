<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FastETL - Login</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="contenedor">
        <div class="logo-area">
            <img src="../img/logo.png" alt="sLogo FastETL">
        </div>

        <div class="form-area">
            <form action="../controlador/login.php" method="post">
                
                <label for="usuario">Usuario:</label>
                <input type="text" name="usuario" required><br>

                <label for="contrasena">Contraseña:</label>
                <input type="password" name="contrasena" required><br>

                <div class="botones">
                    <input type="submit" value="Entrar">
                    <button type="button" onclick="window.location.href='registro.php'">Registrarse</button>
                </div>


                <?php 
                if (isset($_GET['error'])) {
                    echo "<p>Credenciales incorrectas</p>";
                }
                ?>
            </form>
        </div>
    </div>
</body>
</html>