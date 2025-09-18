<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - FastETL</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="contenedor">
        <div class="logo-area">
            <img src="../img/logo.png" alt="sLogo FastETL">
        </div>
        <h2>Crear cuenta</h2>

        <form action="../controlador/registro.php" method="post">
            <label for="cedula">Cédula:</label>
            <input type="text" name="cedula" required>

            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" required>

            <label for="correo">Correo:</label>
            <input type="email" name="correo" required>

            <label for="contrasena">Contraseña:</label>
            <input type="password" name="contrasena" required>

            <label for="confirmar">Confirmar Contraseña:</label>
            <input type="password" name="confirmar" required>

            <label for="rol">Rol:</label>
            <select name="rol" required>
                <option value="Admin">Admin</option>
                <option value="Operador">Operador</option>
                <option value="Desarrollador">Desarrollador</option>
                <option value="Auditor">Auditor</option>
            </select>

             <br>
            <div class="botones">
                <input type="submit" value="Registrar">
                <button type="button" onclick="window.location.href='login.php'">Volver al Login</button>
            </div>

            <br>
        </form>
    

            <?php if (isset($_GET['error'])) echo "<p style='color:red;'>".$_GET['error']."</p>"; ?>
            <?php if (isset($_GET['ok'])) echo "<p style='color:green;'>".$_GET['ok']."</p>"; ?>
            <?php if (isset($_GET['existe'])): ?>
            <div style="text-align:center; margin-top:20px;">
                <p style="color:red; font-weight:bold; font-size:16px;">
                    Ya existe un usuario con el correo: <b><?php echo $_GET['correo']; ?></b>
                </p>
                
            </div>
        <?php endif; ?>

        </div>
    </body>
</html>
