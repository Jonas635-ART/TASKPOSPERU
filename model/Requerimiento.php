<?php
require_once __DIR__ . '/../config/conexion.php';

class Requerimiento {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    // Guardar un nuevo ticket según el nuevo esquema
    public function registrar($id_solicitante, $titulo, $prioridad, $descripcion_tecnica) {
        try {
            $sql = "INSERT INTO requerimientos (titulo, descripcion_tecnica, prioridad, id_solicitante) 
                    VALUES (:titulo, :descripcion_tecnica, :prioridad, :id_solicitante)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':titulo'              => $titulo,
                ':descripcion_tecnica' => $descripcion_tecnica,
                ':prioridad'           => $prioridad,
                ':id_solicitante'      => $id_solicitante
            ]);

            return ['success' => true, 'message' => 'Requerimiento registrado con éxito.'];
        } catch (PDOException $e) {
            error_log("Error al registrar requerimiento: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al guardar el requerimiento.'];
        }
    }

    // Listar tickets del usuario actual
    public function listarPorUsuario($id_solicitante) {
        try {
            $sql = "SELECT id_requerimiento, titulo, descripcion_tecnica, prioridad, estado, fecha_creacion 
                    FROM requerimientos 
                    WHERE id_solicitante = :id_solicitante 
                    ORDER BY fecha_creacion DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_solicitante' => $id_solicitante]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al listar requerimientos: " . $e->getMessage());
            return [];
        }
    }
    // Obtener estadísticas globales para el dashboard del Developer/Admin
    public function obtenerEstadisticasGlobales() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'en proceso' THEN 1 ELSE 0 END) as en_proceso,
                        SUM(CASE WHEN estado = 'terminado' THEN 1 ELSE 0 END) as terminados
                    FROM requerimientos";
            $stmt = $this->pdo->query($sql);
            $resultado = $stmt->fetch();
            
            // Si la tabla está vacía y devuelve nulos, normalizar a ceros
            return [
                'total'      => $resultado['total'] ?? 0,
                'pendientes' => $resultado['pendientes'] ?? 0,
                'en_proceso' => $resultado['en_proceso'] ?? 0,
                'terminados' => $resultado['terminados'] ?? 0
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return ['total' => 0, 'pendientes' => 0, 'en_proceso' => 0, 'terminados' => 0];
        }
    }

    // Listar todos los tickets con sus relaciones para la tabla principal
    public function listarTodos() {
        try {
            $sql = "SELECT r.id_requerimiento, r.titulo, r.descripcion_tecnica, r.prioridad, r.estado, 
                           u1.nombre AS solicitante, u1.rol AS rol_solicitante,
                           u2.nombre AS responsable
                    FROM requerimientos r
                    INNER JOIN usuarios u1 ON r.id_solicitante = u1.id_usuario
                    LEFT JOIN requerimientos_responsables rr ON r.id_requerimiento = rr.id_requerimiento
                    LEFT JOIN usuarios u2 ON rr.id_usuario = u2.id_usuario
                    ORDER BY r.id_requerimiento DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al listar todos los requerimientos: " . $e->getMessage());
            return [];
        }
    }
    public function obtenerDetalle($id_requerimiento) {
        try {
            $sql = "SELECT r.*, u1.nombre AS solicitante, u1.correo AS correo_solicitante, u1.rol AS rol_solicitante,
                           u2.nombre AS responsable, u2.id_usuario AS id_responsable
                    FROM requerimientos r
                    INNER JOIN usuarios u1 ON r.id_solicitante = u1.id_usuario
                    LEFT JOIN requerimientos_responsables rr ON r.id_requerimiento = rr.id_requerimiento
                    LEFT JOIN usuarios u2 ON rr.id_usuario = u2.id_usuario
                    WHERE r.id_requerimiento = :id_requerimiento LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_requerimiento' => $id_requerimiento]);
            return $stmt->fetch();
        } catch (PDOException $e) { return false; }
    }

    public function listarSubtareas($id_requerimiento) {
        try {
            $sql = "SELECT * FROM subtareas WHERE id_requerimiento = :id_requerimiento ORDER BY id_subtarea ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_requerimiento' => $id_requerimiento]);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function listarComentarios($id_requerimiento) {
        try {
            $sql = "SELECT c.*, u.nombre, u.rol FROM comentarios c 
                    INNER JOIN usuarios u ON c.id_usuario = u.id_usuario 
                    WHERE c.id_requerimiento = :id_requerimiento ORDER BY c.fecha_creacion ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_requerimiento' => $id_requerimiento]);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function listarEvidencias($id_requerimiento) {
        try {
            $sql = "SELECT * FROM evidencias WHERE id_requerimiento = :id_requerimiento ORDER BY fecha_subida DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_requerimiento' => $id_requerimiento]);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    // --- ACCIONES AJAX ---

    public function actualizarEstadoTicket($id_requerimiento, $nuevo_estado, $nueva_prioridad) {
        try {
            $sql = "UPDATE requerimientos SET estado = :estado, prioridad = :prioridad, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_requerimiento = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':estado' => $nuevo_estado, ':prioridad' => $nueva_prioridad, ':id' => $id_requerimiento]);
        } catch (PDOException $e) { return false; }
    }

    public function insertarSubtarea($id_requerimiento, $descripcion) {
        try {
            $sql = "INSERT INTO subtareas (id_requerimiento, descripcion) VALUES (:id_req, :desc)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id_req' => $id_requerimiento, ':desc' => $descripcion]);
        } catch (PDOException $e) { return false; }
    }

    public function insertarComentario($id_requerimiento, $id_usuario, $mensaje) {
        try {
            $sql = "INSERT INTO comentarios (id_requerimiento, id_usuario, mensaje) VALUES (:id_req, :id_usr, :msg)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id_req' => $id_requerimiento, ':id_usr' => $id_usuario, ':msg' => $mensaje]);
        } catch (PDOException $e) { return false; }
    }
}
?>