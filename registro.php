
<?php
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";

    if ($nombre === "" || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($contrasena) < 8) {
        $mensaje = "Verifica tus datos. La contraseña debe tener al menos 8 caracteres.";
    } else {
        try {
            $clave_segura = password_hash($contrasena, PASSWORD_DEFAULT);


             $sql = "INSERT INTO usuarios (nombre, correo, password)
             VALUES (?, ?, ?)";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([$nombre, $correo, $clave_segura]);

            $mensaje = "Usuario registrado correctamente.";
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $mensaje = "No se pudo registrar el usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de usuarios</title>
</head>
<link rel="stylesheet" href="estilos.css">
<body>
    <h1>Mi Agenda de Contactos</h1>
    <h2>Registrar usuario</h2>

    <p><?= htmlspecialchars($mensaje) ?></p>

    <form method="POST">
        <label>Nombre:</label>
        <input type="text" name="nombre" required>
        <br><br>

        <label>Correo electrónico:</label>
        <input type="email" name="correo" required>
        <br><br>

        <label>Contraseña:</label>
        <input type="password" name="contrasena" minlength="8" required>
        <br><br>

        <button type="submit">Registrarse</button>
    </form>
</body>
</html>
