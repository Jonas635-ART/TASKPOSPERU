<?php
require_once __DIR__ . '/../config/conexion.php';

if (!defined('APP_INIT')) { http_response_code(403); exit(); }

class Requerimiento {
    // Valores permitidos (deben coincidir con los CHECK de la base de datos)
    public const ESTADOS           = ['pendiente', 'en proceso', 'terminado'];
    public const PRIORIDADES       = ['baja', 'media', 'alta', 'critica'];
    public const ESTADOS_SUBTAREA  = ['pendiente', 'terminado'];

    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    // Guardar un nuevo ticket según el nuevo esquema
    public function registrar($id_proyecto, $titulo, $descripcion_tecnica, $prioridad, $id_solicitante) {
        try {
            // El secreto está en el RETURNING id_requerimiento al final
            $sql = "INSERT INTO requerimientos (id_proyecto, titulo, descripcion_tecnica, prioridad, estado, id_solicitante, fecha_creacion) 
                    VALUES (:id_proyecto, :titulo, :desc, :prioridad, 'pendiente', :id_solicitante, NOW()) 
                    RETURNING id_requerimiento";
                    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_proyecto' => $id_proyecto, // PostgreSQL maneja automáticamente los nulos aquí
                ':titulo' => trim($titulo),
                ':desc' => trim($descripcion_tecnica),
                ':prioridad' => strtolower(trim($prioridad)),
                ':id_solicitante' => (int)$id_solicitante
            ]);
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si todo salió bien, devolvemos el número de ID directamente
            return $resultado ? (int)$resultado['id_requerimiento'] : false; 
            
        } catch (PDOException $e) {
            error_log("Error al registrar requerimiento: " . $e->getMessage());
            return false;
        }
    }
    // Listar tickets del usuario actual
    public function listarPorUsuario($id_solicitante) {
        try {
            $sql = "SELECT r.*, u2.nombre AS responsable
                    FROM requerimientos r
                    LEFT JOIN requerimientos_responsables rr ON r.id_requerimiento = rr.id_requerimiento
                    LEFT JOIN usuarios u2 ON rr.id_usuario = u2.id_usuario
                    WHERE r.id_solicitante = :id_solicitante 
                    ORDER BY r.fecha_creacion DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_solicitante' => (int)$id_solicitante]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) { 
                error_log("Error en listarPorUsuario: " . $e->getMessage());
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


    // Listar TODOS los tickets del sistema (Explícito para evitar pérdida de columnas)
    public function listarTodos() {
        try {
            $sql = "SELECT r.id_requerimiento, r.titulo, r.descripcion_tecnica, r.prioridad, r.estado, r.fecha_creacion, 
                           u1.nombre AS solicitante, u1.correo AS correo_solicitante, u1.rol AS rol_solicitante, 
                           u2.nombre AS responsable
                    FROM requerimientos r
                    INNER JOIN usuarios u1 ON r.id_solicitante = u1.id_usuario
                    LEFT JOIN requerimientos_responsables rr ON r.id_requerimiento = rr.id_requerimiento
                    LEFT JOIN usuarios u2 ON rr.id_usuario = u2.id_usuario
                    ORDER BY r.fecha_creacion DESC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
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

    // Si $nueva_prioridad es null se conserva la prioridad actual (p. ej. al mover en el Kanban)
    public function actualizarEstadoTicket($id_requerimiento, $nuevo_estado, $nueva_prioridad = null) {
        try {
            $sql = "UPDATE requerimientos SET estado = :estado, prioridad = COALESCE(:prioridad, prioridad), fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_requerimiento = :id";
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
    // Listar usuarios que son desarrolladores o administradores para poder asignarles tickets
    public function listarTecnicos() {
        try {
            $sql = "SELECT id_usuario, nombre, correo, rol FROM usuarios WHERE rol IN ('admin', 'developer') ORDER BY nombre ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    // Asignar un responsable principal al requerimiento
    public function asignarResponsableTicket($id_requerimiento, $id_usuario) {
        try {
            // Primero eliminamos si ya tenía un responsable previo para evitar duplicados
            $this->pdo->prepare("DELETE FROM requerimientos_responsables WHERE id_requerimiento = ?")->execute([$id_requerimiento]);
            
            // Insertamos el nuevo responsable
            $sql = "INSERT INTO requerimientos_responsables (id_requerimiento, id_usuario) VALUES (?, ?)";
            return $this->pdo->prepare($sql)->execute([$id_requerimiento, $id_usuario]);
        } catch (PDOException $e) { return false; }
    }

    // Cambiar estado de subtarea (Terminado / Pendiente)
    public function actualizarEstadoSubtarea($id_subtarea, $estado) {
        try {
            $sql = "UPDATE subtareas SET estado = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_subtarea = ?";
            return $this->pdo->prepare($sql)->execute([$estado, $id_subtarea]);
        } catch (PDOException $e) { return false; }
    }

    // Obtener tickets formateados para el tablero Kanban con conteo de subtareas
    public function listarParaKanban() {
        try {
            $sql = "SELECT r.id_requerimiento, r.titulo, r.descripcion_tecnica, r.prioridad, r.estado, r.fecha_creacion,
                           u1.nombre AS solicitante,
                           u2.nombre AS responsable,
                           (SELECT COUNT(*) FROM subtareas s WHERE s.id_requerimiento = r.id_requerimiento) AS total_subtareas,
                           (SELECT COUNT(*) FROM subtareas s WHERE s.id_requerimiento = r.id_requerimiento AND s.estado = 'terminado') AS subtareas_completadas
                    FROM requerimientos r
                    INNER JOIN usuarios u1 ON r.id_solicitante = u1.id_usuario
                    LEFT JOIN requerimientos_responsables rr ON r.id_requerimiento = rr.id_requerimiento
                    LEFT JOIN usuarios u2 ON rr.id_usuario = u2.id_usuario
                    ORDER BY r.id_requerimiento DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarParaKanban: " . $e->getMessage());
            return [];
        }
    }
    // Obtener notificaciones para Admin/Dev (Últimos tickets pendientes)
    public function obtenerNotificacionesTecnicos() {
        try {
            $sql = "SELECT id_requerimiento, titulo, fecha_creacion 
                    FROM requerimientos 
                    WHERE estado = 'pendiente' 
                    ORDER BY fecha_creacion DESC LIMIT 5";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    // Obtener notificaciones para el Cliente (Últimos tickets terminados)
    public function obtenerNotificacionesCliente($id_cliente) {
        try {
            $sql = "SELECT id_requerimiento, titulo, fecha_actualizacion 
                    FROM requerimientos 
                    WHERE estado = 'terminado' AND id_solicitante = ? 
                    ORDER BY fecha_actualizacion DESC LIMIT 5";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_cliente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }
    
    // ==========================================================
    // Tickets <-> Proyectos
    // ==========================================================

    // Tickets que pertenecen a un proyecto
    public function listarPorProyecto($id_proyecto) {
        try {
            $sql = "SELECT r.id_requerimiento, r.titulo, r.prioridad, r.estado, r.fecha_creacion,
                           u2.nombre AS responsable
                    FROM requerimientos r
                    LEFT JOIN requerimientos_responsables rr ON r.id_requerimiento = rr.id_requerimiento
                    LEFT JOIN usuarios u2 ON rr.id_usuario = u2.id_usuario
                    WHERE r.id_proyecto = :id_proyecto
                    ORDER BY r.fecha_creacion DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_proyecto' => (int)$id_proyecto]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarPorProyecto: " . $e->getMessage());
            return [];
        }
    }

    // Tickets que se pueden agregar a un proyecto: los que no tienen proyecto
    // y los que están en otro proyecto (se indica cuál para que el usuario sepa que se moverá)
    public function listarDisponiblesParaProyecto($id_proyecto) {
        try {
            $sql = "SELECT r.id_requerimiento, r.titulo, r.prioridad, r.estado,
                           p.nombre_proyecto AS proyecto_actual
                    FROM requerimientos r
                    LEFT JOIN proyectos p ON r.id_proyecto = p.id_proyecto
                    WHERE r.id_proyecto IS NULL OR r.id_proyecto <> :id_proyecto
                    ORDER BY (r.id_proyecto IS NULL) DESC, r.fecha_creacion DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_proyecto' => (int)$id_proyecto]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarDisponiblesParaProyecto: " . $e->getMessage());
            return [];
        }
    }

    // Asigna un ticket a un proyecto, o lo deja sin proyecto si $id_proyecto es null
    public function asignarProyecto($id_requerimiento, $id_proyecto) {
        try {
            $sql = "UPDATE requerimientos SET id_proyecto = :id_proyecto, fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_requerimiento = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_proyecto', $id_proyecto, $id_proyecto === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id', (int)$id_requerimiento, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en asignarProyecto: " . $e->getMessage());
            return false;
        }
    }

    // Tickets donde el usuario es el responsable asignado
    public function listarAsignadosA($id_usuario) {
        try {
            $sql = "SELECT r.id_requerimiento, r.titulo, r.descripcion_tecnica, r.prioridad, r.estado,
                           r.fecha_creacion, r.fecha_actualizacion,
                           u1.nombre AS solicitante,
                           p.nombre_proyecto AS proyecto,
                           (SELECT COUNT(*) FROM subtareas s WHERE s.id_requerimiento = r.id_requerimiento) AS total_subtareas,
                           (SELECT COUNT(*) FROM subtareas s WHERE s.id_requerimiento = r.id_requerimiento AND s.estado = 'terminado') AS subtareas_completadas
                    FROM requerimientos_responsables rr
                    INNER JOIN requerimientos r ON rr.id_requerimiento = r.id_requerimiento
                    INNER JOIN usuarios u1 ON r.id_solicitante = u1.id_usuario
                    LEFT JOIN proyectos p ON r.id_proyecto = p.id_proyecto
                    WHERE rr.id_usuario = :id_usuario
                    ORDER BY CASE r.estado WHEN 'en proceso' THEN 0 WHEN 'pendiente' THEN 1 ELSE 2 END,
                             CASE r.prioridad WHEN 'critica' THEN 0 WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END,
                             r.fecha_creacion DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_usuario' => (int)$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarAsignadosA: " . $e->getMessage());
            return [];
        }
    }

    public function eliminarDefinitivamente($id_requerimiento) {
        try {
            $this->pdo->beginTransaction();

            // 1. Eliminar responsables de subtareas asociadas al ticket
            $sqlSubResp = "DELETE FROM subtareas_responsables 
                           WHERE id_subtarea IN (SELECT id_subtarea FROM subtareas WHERE id_requerimiento = ?)";
            $stmt = $this->pdo->prepare($sqlSubResp);
            $stmt->execute([$id_requerimiento]);

            // 2. Eliminar subtareas del ticket
            $stmt = $this->pdo->prepare("DELETE FROM subtareas WHERE id_requerimiento = ?");
            $stmt->execute([$id_requerimiento]);

            // 3. Eliminar comentarios del ticket
            $stmt = $this->pdo->prepare("DELETE FROM comentarios WHERE id_requerimiento = ?");
            $stmt->execute([$id_requerimiento]);

            // 4. Eliminar responsables asignados al ticket
            $stmt = $this->pdo->prepare("DELETE FROM requerimientos_responsables WHERE id_requerimiento = ?");
            $stmt->execute([$id_requerimiento]);

            // 5. Eliminar el ticket principal
            $stmt = $this->pdo->prepare("DELETE FROM requerimientos WHERE id_requerimiento = ?");
            $stmt->execute([$id_requerimiento]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

}
?>