
<?php
$servidor = "127.0.0.1";
$puerto = 3307;
$usuario = "root";
$contrasena = "";
$base_datos = "agenda_contactos";

try {
    $conexion = new PDO(
        "mysql:host=$servidor;port=$puerto;dbname=$base_datos;charset=utf8mb4",
        $usuario,
        $contrasena
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );


} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "Error al conectar con la base de datos";
}
?>
