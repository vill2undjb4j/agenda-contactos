
<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once "conexion.php";

$usuario_id = $_SESSION["usuario_id"];
$mensaje = "";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

// Buscar el contacto del usuario
$sql = "SELECT * FROM contactos
        WHERE id = ? AND usuario_id = ?";

$stmt = $conexion->prepare($sql);
$stmt->execute([$id, $usuario_id]);

$contacto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contacto) {
    header("Location: index.php");
    exit;
}

// ACTUALIZAR CONTACTO
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $cedula = trim($_POST["cedula"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $ciudad = trim($_POST["ciudad"] ?? "");

    if ($nombre === "" || $telefono === "") {
        $mensaje = "Nombre y teléfono son obligatorios.";

    } elseif (
        mb_strlen($nombre) > 100 ||
        mb_strlen($telefono) > 20 ||
        mb_strlen($correo) > 150 ||
        mb_strlen($ciudad) > 100
    ) {
        $mensaje = "Uno de los campos es demasiado largo.";

    } elseif (
        $cedula !== "" &&
        !preg_match('/^[0-9]{10}$/', $cedula)
    ) {
        $mensaje = "La cédula debe tener exactamente 10 dígitos.";

    } elseif (
        $correo !== "" &&
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
    ) {
        $mensaje = "El correo no es válido.";

    } else {
        try {
            $sql = "UPDATE contactos
                    SET nombre = ?,
                        cedula = ?,
                        telefono = ?,
                        correo = ?,
                        ciudad = ?
                    WHERE id = ? AND usuario_id = ?";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                $nombre,
                $cedula !== "" ? $cedula : null,
                $telefono,
                $correo !== "" ? $correo : null,
                $ciudad !== "" ? $ciudad : null,
                $id,
                $usuario_id
            ]);

            $_SESSION["mensaje"] = "Contacto actualizado correctamente.";

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $mensaje = "No se pudo actualizar el contacto.";
        }
    }

    // Conservar los datos escritos si hay errores
    $contacto["nombre"] = $nombre;
    $contacto["cedula"] = $cedula;
    $contacto["telefono"] = $telefono;
    $contacto["correo"] = $correo;
    $contacto["ciudad"] = $ciudad;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar contacto</title>
</head>
<link rel="stylesheet" href="estilos.css">
<body>

<h1>Editar contacto</h1>

<?php if ($mensaje !== ""): ?>
    <p><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<form method="POST">

    <label>Nombre:</label>
    <input
        type="text"
        name="nombre"
        maxlength="100"
        value="<?= htmlspecialchars($contacto["nombre"], ENT_QUOTES, 'UTF-8') ?>"
        required
    >
    <br><br>

    <label>Cédula:</label>
    <input
        type="text"
        name="cedula"
        maxlength="10"
        pattern="[0-9]{10}"
        inputmode="numeric"
        value="<?= htmlspecialchars($contacto["cedula"] ?? "", ENT_QUOTES, 'UTF-8') ?>"
    >
    <br><br>

    <label>Teléfono:</label>
    <input
        type="tel"
        name="telefono"
        maxlength="20"
        value="<?= htmlspecialchars($contacto["telefono"], ENT_QUOTES, 'UTF-8') ?>"
        required
    >
    <br><br>

    <label>Correo:</label>
    <input
        type="email"
        name="correo"
        maxlength="150"
        value="<?= htmlspecialchars($contacto["correo"] ?? "", ENT_QUOTES, 'UTF-8') ?>"
    >
    <br><br>

    <label>Ciudad:</label>
    <input
        type="text"
        name="ciudad"
        maxlength="100"
        value="<?= htmlspecialchars($contacto["ciudad"] ?? "", ENT_QUOTES, 'UTF-8') ?>"
    >
    <br><br>

    <button type="submit">Guardar cambios</button>

</form>

<p>
    <a href="index.php">Volver a mi agenda</a>
</p>

</body>
</html>
