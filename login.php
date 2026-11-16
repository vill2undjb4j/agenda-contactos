
<?php
session_start();
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";

    if ($correo === "" || $contrasena === "") {
        $mensaje = "Completa todos los campos.";
    } else {
        try {
            $sql = "SELECT id, nombre, password
                    FROM usuarios
                    WHERE correo = ?";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([$correo]);

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify(
                $contrasena,
                $usuario["password"]
            )) {
                session_regenerate_id(true);

                $_SESSION["usuario_id"] = $usuario["id"];
                $_SESSION["nombre"] = $usuario["nombre"];

                header("Location: index.php");
                exit;
            } else {
                $mensaje = "Correo o contraseña incorrectos.";
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $mensaje = "Ocurrió un error al iniciar sesión.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - Agenda de Contactos</title>
</head>
<link rel="stylesheet" href="estilos.css">
<body>

    <h1>Mi Agenda de Contactos</h1>
    <h2>Iniciar sesión</h2>

    <p><?= htmlspecialchars($mensaje) ?></p>

    <form method="POST">

        <label>Correo electrónico:</label>
        <input type="email" name="correo" required>
        <br><br>

        <label>Contraseña:</label>
        <input type="password" name="contrasena" required>
        <br><br>

        <button type="submit">Ingresar</button>

    </form>

    <p>
        ¿No tienes cuenta?
        <a href="registro.php">Regístrate aquí</a>
    </p>

</body>
</html>
