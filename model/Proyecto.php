<?php
require_once __DIR__ . '/../config/conexion.php';

if (!defined('APP_INIT')) { http_response_code(403); exit(); }

class Proyecto {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function listarProyectos() {
        try {
            // Extrae los datos del proyecto y concatena el ID y Nombre de sus miembros directos
            $sql = "SELECT p.*,
                        (
                            SELECT STRING_AGG(u.nombre || ':' || u.id_usuario, ',')
                            FROM proyectos_miembros pm
                            INNER JOIN usuarios u ON pm.id_usuario = u.id_usuario
                            WHERE pm.id_proyecto = p.id_proyecto
                        ) AS miembros_data
                    FROM proyectos p
                    ORDER BY p.fecha_actualizacion DESC";
            
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }
    // Listar posibles líderes (Admins y Developers)
    public function listarUsuariosTecnicos() {
        $sql = "SELECT id_usuario, nombre FROM usuarios WHERE rol IN ('admin', 'developer') ORDER BY nombre ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear o Editar un proyecto
    public function guardar($id, $nombre, $descripcion, $miembros_ids) {
        try {
            $this->pdo->beginTransaction();

            if ($id) {
                $sql = "UPDATE proyectos SET nombre_proyecto = ?, descripcion = ?, fecha_actualizacion = NOW() WHERE id_proyecto = ?";
                $this->pdo->prepare($sql)->execute([$nombre, $descripcion, $id]);
                $id_proyecto = $id;
            } else {
                $sql = "INSERT INTO proyectos (nombre_proyecto, descripcion) VALUES (?, ?) RETURNING id_proyecto";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nombre, $descripcion]);
                $id_proyecto = $stmt->fetchColumn();
            }

            // Actualizar los miembros (borramos los anteriores y guardamos los nuevos)
            $this->pdo->prepare("DELETE FROM proyectos_miembros WHERE id_proyecto = ?")->execute([$id_proyecto]);
            
            if (!empty($miembros_ids) && is_array($miembros_ids)) {
                $stmt_miembros = $this->pdo->prepare("INSERT INTO proyectos_miembros (id_proyecto, id_usuario) VALUES (?, ?)");
                foreach ($miembros_ids as $id_usuario) {
                    $stmt_miembros->execute([$id_proyecto, $id_usuario]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) { 
            $this->pdo->rollBack();
            error_log("Error al guardar proyecto: " . $e->getMessage());
            return false; 
        }
    }

    // Eliminar un proyecto
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM proyectos WHERE id_proyecto = ?";
            return $this->pdo->prepare($sql)->execute([$id]);
        } catch (PDOException $e) { return false; }
    }
}
?>