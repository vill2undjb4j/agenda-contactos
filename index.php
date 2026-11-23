
<?php
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
    http_response_code(403); exit('Solicitud no válida.');
}

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once "conexion.php";

$usuario_id = $_SESSION["usuario_id"];
$mensaje = "";

// REGISTRAR UN NUEVO CONTACTO
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $cedula = trim($_POST["cedula"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $ciudad = trim($_POST["ciudad"] ?? "");

    if ($nombre === "" || $telefono === "") {
        $mensaje = "Completa el nombre y teléfono.";

    } elseif (
        mb_strlen($nombre) > 100 ||
        mb_strlen($telefono) > 20 ||
        mb_strlen($correo) > 150 ||
        mb_strlen($ciudad) > 100
    ) {
        $mensaje = "Uno de los campos supera la longitud permitida.";

    } elseif (
        $cedula !== "" &&
        !preg_match('/^[0-9]{10}$/', $cedula)
    ) {
        $mensaje = "La cédula debe contener exactamente 10 dígitos.";

    } elseif (
        $correo !== "" &&
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
    ) {
        $mensaje = "Ingresa un correo válido.";

    } else {
        try {
            $sql = "INSERT INTO contactos
                    (usuario_id, nombre, cedula, telefono, correo, ciudad)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                $usuario_id,
                $nombre,
                $cedula !== "" ? $cedula : null,
                $telefono,
                $correo !== "" ? $correo : null,
                $ciudad !== "" ? $ciudad : null
            ]);

            $_SESSION["mensaje"] = "Contacto guardado correctamente.";

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $mensaje = "No se pudo guardar el contacto.";
        }
    }
}

// MOSTRAR MENSAJE DESPUÉS DE GUARDAR
if (isset($_SESSION["mensaje"])) {
    $mensaje = $_SESSION["mensaje"];
    unset($_SESSION["mensaje"]);
}

// BUSCAR CONTACTOS POR NOMBRE, TELÉFONO O CÉDULA
$busqueda = trim($_GET["buscar"] ?? "");

$sql_contactos = "SELECT id, nombre, cedula, telefono, correo, ciudad, favorito, fecha_creacion
                  FROM contactos
                  WHERE usuario_id = ?";

$parametros = [$usuario_id];

if ($busqueda !== "") {

    $sql_contactos .= " AND (
        nombre LIKE ?
        OR telefono LIKE ?
        OR cedula LIKE ?
        OR correo LIKE ?
        OR ciudad LIKE ?
    )";

    $termino = "%" . $busqueda . "%";

    $parametros[] = $termino;
    $parametros[] = $termino;
    $parametros[] = $termino;
    $parametros[] = $termino;
    $parametros[] = $termino;
}

$orden = $_GET['orden'] ?? 'fecha';
$ordenes = ['nombre'=>'nombre ASC, id ASC', 'ciudad'=>'ciudad ASC, nombre ASC, id ASC', 'fecha'=>'fecha_creacion DESC, id DESC'];
if (!is_string($orden) || !isset($ordenes[$orden])) $orden = 'fecha';
$soloFavoritos = ($_GET['favoritos'] ?? '') === '1';
if ($soloFavoritos) $sql_contactos .= ' AND favorito = 1';
$sql_contactos .= ' ORDER BY ' . $ordenes[$orden];

$stmt_contactos = $conexion->prepare($sql_contactos);
$stmt_contactos->execute($parametros);

$contactos = $stmt_contactos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi Agenda de Contactos</title>

    <!-- CONEXIÓN CON NUESTRO ARCHIVO CSS -->
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <h1>Mi Agenda de Contactos</h1>

    <!-- CERRAR SESIÓN -->
    <form action="logout.php" method="POST">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
        <button type="submit">Cerrar sesión</button>
    </form>

    <p>
        Bienvenido,
        <strong>
            <?= htmlspecialchars($_SESSION["nombre"] ?? "Usuario", ENT_QUOTES, 'UTF-8') ?>
        </strong>
    </p>

    <hr>

    <h2>Registrar nuevo contacto</h2>

    <?php if ($mensaje !== ""): ?>
        <p>
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <!-- FORMULARIO DE REGISTRO -->
    <form action="index.php" method="POST">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

        <label for="nombre">Nombre:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            required
        >
        <br><br>

        <label for="cedula">Cédula:</label>
        <input
            type="text"
            id="cedula"
            name="cedula"
            maxlength="10"
            pattern="[0-9]{10}"
            inputmode="numeric"
            placeholder="10 dígitos"
        >
        <br><br>

        <label for="telefono">Teléfono:</label>
        <input
            type="tel"
            id="telefono"
            name="telefono"
            maxlength="20"
            required
        >
        <br><br>

        <label for="correo">Correo:</label>
        <input
            type="email"
            id="correo"
            name="correo"
            maxlength="150"
        >
        <br><br>

        <label for="ciudad">Ciudad:</label>
        <input
            type="text"
            id="ciudad"
            name="ciudad"
            maxlength="100"
        >
        <br><br>

        <button type="submit">
            Guardar contacto
        </button>

    </form>

    <hr>

    <h2>Mis contactos registrados</h2>

    <!-- BUSCADOR DE CONTACTOS -->
    <form action="index.php" method="GET">

        <label for="buscar">Buscar contacto:</label>

        <input
            type="text"
            id="buscar"
            name="buscar"
            placeholder="Nombre, correo, ciudad, teléfono o cédula"
            value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>"
        >

        <br><br>

        <label for="orden">Ordenar por:</label>
        <select name="orden" id="orden">
        <?php foreach (['nombre'=>'Nombre (A-Z)', 'ciudad'=>'Ciudad (A-Z)', 'fecha'=>'Más recientes'] as $clave=>$etiqueta): ?>
          <option value="<?= $clave ?>" <?= $orden === $clave ? 'selected' : '' ?>><?= $etiqueta ?></option>
        <?php endforeach; ?>
        </select>
        <label><input type="checkbox" name="favoritos" value="1" <?= $soloFavoritos ? 'checked' : '' ?>> Solo favoritos</label>
        <button type="submit">Aplicar filtros</button>

        <a href="index.php">Mostrar todos</a>

    </form>

    <br>

    <!-- TABLA DE CONTACTOS -->
    <?php if (count($contactos) > 0): ?>

        <table>

            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Ciudad</th>
                    <th>Fecha de creación</th><th>Favorito</th><th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($contactos as $contacto): ?>

                    <tr>
                        <td>
                            <?= htmlspecialchars($contacto["nombre"], ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($contacto["cedula"] ?? "", ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($contacto["telefono"], ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($contacto["correo"] ?? "", ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($contacto["ciudad"] ?? "", ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td><?= htmlspecialchars($contacto['fecha_creacion']) ?></td>
                        <td><form action="favorito.php" method="POST">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                          <input type="hidden" name="id" value="<?= (int)$contacto['id'] ?>">
                          <button class="estrella" type="submit"><?= $contacto['favorito'] ? '★ Sí · Quitar' : '☆ No · Marcar' ?></button>
                        </form></td>
                        <td>
                            <!-- EDITAR CONTACTO -->
                            <a href="editar.php?id=<?= (int)$contacto["id"] ?>">
                                Editar
                            </a>

                            &nbsp;

                            <!-- ELIMINAR CONTACTO -->
                            <form
                                action="eliminar.php"
                                method="POST"
                                style="display:inline;"
                                onsubmit="return confirm('¿Seguro que deseas eliminar este contacto?');"
                            >
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$contacto["id"] ?>"
                                >

                                <button type="submit">
                                    Eliminar
                                </button>

                            </form>

                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php else: ?>

        <?php if ($busqueda !== "" || $soloFavoritos): ?>

            <p>
                No se encontraron contactos con los filtros seleccionados.
            </p>

        <?php else: ?>

            <p>
                Todavía no tienes contactos registrados.
            </p>

        <?php endif; ?>

    <?php endif; ?>

</body>
</html>
