<?php
require_once "config/conexion.php";

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->query("SELECT id_usuario, nombre, correo, rol FROM usuarios");
$usuarios = $stmt->fetchAll();

echo "<h2>Conexión exitosa a PostgreSQL. Usuarios encontrados:</h2>";
echo "<pre>";
print_r($usuarios);
echo "</pre>";