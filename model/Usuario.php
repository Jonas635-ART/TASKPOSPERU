<?php
require_once __DIR__ . '/../config/conexion.php';

class Usuario {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function obtenerPorCorreo($correo) {
        try {
            $stmt = $this->pdo->prepare("SELECT id_usuario, nombre, correo, password, rol FROM usuarios WHERE correo = :correo LIMIT 1");
            $stmt->execute([':correo' => $correo]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en modelo Usuario: " . $e->getMessage());
            return false;
        }
    }
    public function registrar($nombre, $correo, $password, $rol = 'cliente') {
        try {
            if ($this->obtenerPorCorreo($correo)) {
                return ['success' => false, 'message' => 'El correo electrónico ya se encuentra registrado.'];
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO usuarios (nombre, correo, password, rol) VALUES (:nombre, :correo, :password, :rol)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre'   => $nombre,
                ':correo'   => $correo,
                ':password' => $passwordHash,
                ':rol'      => $rol
            ]);

            return ['success' => true, 'message' => 'Usuario registrado exitosamente.'];
        } catch (PDOException $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al procesar el registro en el servidor.'];
        }
    }
}
?>