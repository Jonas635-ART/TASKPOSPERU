<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
require_once __DIR__ . '/../config/conexion.php';

class Usuario {
    private $pdo;
    private const MAX_INTENTOS_CODIGO = 5;
    private const MINUTOS_VALIDEZ     = 15;
    private const COOLDOWN_SEGUNDOS   = 60;

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

    public function obtenerPorId(int $id) {
        try {
            $stmt = $this->pdo->prepare("SELECT id_usuario, nombre, correo, password, rol FROM usuarios WHERE id_usuario = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================================
    // Gestión de cuenta propia (correo, contraseña y eliminación)
    // ==========================================================

    public function actualizarCorreo(int $idUsuario, string $nuevoCorreo, string $passwordActual): array {
        try {
            $usuario = $this->obtenerPorId($idUsuario);
            if (!$usuario) {
                return ['success' => false, 'message' => 'Usuario no encontrado.'];
            }

            if (!password_verify($passwordActual, $usuario['password'])) {
                return ['success' => false, 'message' => 'La contraseña actual es incorrecta.'];
            }

            if (strcasecmp($usuario['correo'], $nuevoCorreo) === 0) {
                return ['success' => false, 'message' => 'El nuevo correo es igual al actual.'];
            }

            $existente = $this->obtenerPorCorreo($nuevoCorreo);
            if ($existente && (int)$existente['id_usuario'] !== $idUsuario) {
                return ['success' => false, 'message' => 'Ese correo electrónico ya está registrado en otra cuenta.'];
            }

            $stmt = $this->pdo->prepare("UPDATE usuarios SET correo = :correo WHERE id_usuario = :id");
            $stmt->execute([':correo' => $nuevoCorreo, ':id' => $idUsuario]);

            return [
                'success' => true,
                'message' => 'Correo electrónico actualizado correctamente.',
                'correo'  => $nuevoCorreo,
            ];
        } catch (PDOException $e) {
            error_log("Error en actualizarCorreo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al actualizar el correo.'];
        }
    }

    public function actualizarPassword(int $idUsuario, string $passwordActual, string $nuevaPassword): array {
        try {
            $usuario = $this->obtenerPorId($idUsuario);
            if (!$usuario) {
                return ['success' => false, 'message' => 'Usuario no encontrado.'];
            }

            if (!password_verify($passwordActual, $usuario['password'])) {
                return ['success' => false, 'message' => 'La contraseña actual es incorrecta.'];
            }

            $nuevoHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("UPDATE usuarios SET password = :pwd WHERE id_usuario = :id");
            $stmt->execute([':pwd' => $nuevoHash, ':id' => $idUsuario]);

            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente.',
            ];
        } catch (PDOException $e) {
            error_log("Error en actualizarPassword: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al actualizar la contraseña.'];
        }
    }

    public function eliminarCuenta(int $idUsuario, string $passwordActual): array {
        try {
            $usuario = $this->obtenerPorId($idUsuario);
            if (!$usuario) {
                return ['success' => false, 'message' => 'Usuario no encontrado.'];
            }

            if (!password_verify($passwordActual, $usuario['password'])) {
                return ['success' => false, 'message' => 'La contraseña actual es incorrecta.'];
            }

            $this->pdo->beginTransaction();

            // 1. Obtener todos los requerimientos creados por este usuario
            $stmtReqs = $this->pdo->prepare("SELECT id_requerimiento FROM requerimientos WHERE id_solicitante = :id");
            $stmtReqs->execute([':id' => $idUsuario]);
            $ticketIds = $stmtReqs->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($ticketIds)) {
                $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));

                // Eliminar dependencias de los tickets del usuario
                $this->pdo->prepare("DELETE FROM subtareas_responsables WHERE id_subtarea IN (SELECT id_subtarea FROM subtareas WHERE id_requerimiento IN ($placeholders))")
                          ->execute($ticketIds);
                $this->pdo->prepare("DELETE FROM subtareas WHERE id_requerimiento IN ($placeholders)")
                          ->execute($ticketIds);
                $this->pdo->prepare("DELETE FROM comentarios WHERE id_requerimiento IN ($placeholders)")
                          ->execute($ticketIds);
                $this->pdo->prepare("DELETE FROM requerimientos_responsables WHERE id_requerimiento IN ($placeholders)")
                          ->execute($ticketIds);
                $this->pdo->prepare("DELETE FROM requerimientos WHERE id_requerimiento IN ($placeholders)")
                          ->execute($ticketIds);
            }

            // 2. Eliminar comentarios que el usuario haya dejado en otros tickets
            $this->pdo->prepare("DELETE FROM comentarios WHERE id_usuario = :id")
                      ->execute([':id' => $idUsuario]);

            // 3. Desvincular si fuese líder de algún proyecto
            $this->pdo->prepare("UPDATE proyectos SET id_lider = NULL WHERE id_lider = :id")
                      ->execute([':id' => $idUsuario]);

            // 4. Eliminar el usuario (las tablas con ON DELETE CASCADE / SET NULL se limpian automáticamente)
            $this->pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id")
                      ->execute([':id' => $idUsuario]);

            $this->pdo->commit();

            return [
                'success'            => true,
                'message'            => 'Tu cuenta ha sido eliminada correctamente.',
                'tickets_eliminados' => array_map('intval', $ticketIds),
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error en eliminarCuenta: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al intentar eliminar la cuenta.'];
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
                ':rol'      => $rol,
            ]);

            return ['success' => true, 'message' => 'Usuario registrado exitosamente.'];
        } catch (PDOException $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al procesar el registro en el servidor.'];
        }
    }

    // ==========================================================
    // Recuperación autónoma de contraseña por correo (código 6 dígitos)
    // ==========================================================

    // 1. Genera un código de 6 dígitos (válido 15 min) para enviarlo al correo del usuario
    public function generarCodigoRecuperacion(string $correo): array {
        try {
            $usuario = $this->obtenerPorCorreo($correo);
            if (!$usuario) {
                return ['existe' => false];
            }

            // Evitar spam: si ya se envió un código hace menos de 60 segundos
            $stmtRecent = $this->pdo->prepare("
                SELECT EXTRACT(EPOCH FROM (NOW() - fecha_solicitud)) AS segundos
                FROM password_resets
                WHERE id_usuario = :id AND estado = 'codigo_generado'
                ORDER BY fecha_solicitud DESC
                LIMIT 1
            ");
            $stmtRecent->execute([':id' => $usuario['id_usuario']]);
            $segundos = $stmtRecent->fetchColumn();
            if ($segundos !== false && (float)$segundos < self::COOLDOWN_SEGUNDOS) {
                $espera = max(1, (int)ceil(self::COOLDOWN_SEGUNDOS - (float)$segundos));
                return [
                    'existe'   => true,
                    'cooldown' => true,
                    'espera'   => $espera,
                ];
            }

            $codigoPlano = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $codigoHash  = password_hash($codigoPlano, PASSWORD_BCRYPT);

            $this->pdo->beginTransaction();

            $this->pdo->prepare("
                DELETE FROM password_resets
                WHERE id_usuario = :id AND estado IN ('pendiente', 'codigo_generado')
            ")->execute([':id' => $usuario['id_usuario']]);

            $sql = "INSERT INTO password_resets (id_usuario, estado, codigo_hash, expira_en, intentos)
                    VALUES (:id_usr, 'codigo_generado', :hash, NOW() + INTERVAL '" . self::MINUTOS_VALIDEZ . " minutes', 0)";
            $this->pdo->prepare($sql)->execute([
                ':id_usr' => $usuario['id_usuario'],
                ':hash'   => $codigoHash,
            ]);

            $this->pdo->commit();

            return [
                'existe'  => true,
                'codigo'  => $codigoPlano,
                'minutos' => self::MINUTOS_VALIDEZ,
                'usuario' => $usuario,
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error en generarCodigoRecuperacion: " . $e->getMessage());
            return ['existe' => false, 'error' => true];
        }
    }

    // 2. Valida el código de 6 dígitos y actualiza la contraseña
    public function restablecerConCodigo(string $correo, string $codigo, string $nuevaPassword): array {
        try {
            $usuario = $this->obtenerPorCorreo($correo);
            if (!$usuario) {
                return ['success' => false, 'message' => 'Correo o código de verificación inválido o expirado.'];
            }

            $stmt = $this->pdo->prepare("
                SELECT id_reset, codigo_hash, intentos,
                       (expira_en IS NOT NULL AND expira_en > NOW()) AS vigente
                FROM password_resets
                WHERE id_usuario = :id AND estado = 'codigo_generado'
                ORDER BY fecha_solicitud DESC
                LIMIT 1
            ");
            $stmt->execute([':id' => $usuario['id_usuario']]);
            $reset = $stmt->fetch();

            if (!$reset || empty($reset['codigo_hash'])) {
                return ['success' => false, 'message' => 'No hay un código activo para esta cuenta. Solicita uno nuevo.'];
            }

            if (empty($reset['vigente'])) {
                $this->pdo->prepare("DELETE FROM password_resets WHERE id_reset = :id")->execute([':id' => $reset['id_reset']]);
                return ['success' => false, 'message' => 'El código ha expirado (validez de ' . self::MINUTOS_VALIDEZ . ' min). Solicita uno nuevo.'];
            }

            $codigoNormalizado = preg_replace('/\s+/', '', trim($codigo));
            if (!password_verify($codigoNormalizado, $reset['codigo_hash'])) {
                $intentos = ((int)$reset['intentos']) + 1;
                if ($intentos >= self::MAX_INTENTOS_CODIGO) {
                    $this->pdo->prepare("DELETE FROM password_resets WHERE id_reset = :id")->execute([':id' => $reset['id_reset']]);
                    return ['success' => false, 'message' => 'Demasiados intentos fallidos. El código fue anulado; solicita uno nuevo.'];
                }

                $this->pdo->prepare("UPDATE password_resets SET intentos = :i WHERE id_reset = :id")
                          ->execute([':i' => $intentos, ':id' => $reset['id_reset']]);
                $restantes = self::MAX_INTENTOS_CODIGO - $intentos;
                return ['success' => false, 'message' => "Código incorrecto ({$restantes} intentos restantes)."];
            }

            $this->pdo->beginTransaction();

            $nuevoHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);
            $this->pdo->prepare("UPDATE usuarios SET password = :pwd WHERE id_usuario = :id")
                      ->execute([':pwd' => $nuevoHash, ':id' => $usuario['id_usuario']]);

            $this->pdo->prepare("
                UPDATE password_resets
                SET estado = 'completado',
                    codigo_hash = NULL,
                    fecha_resolucion = CURRENT_TIMESTAMP
                WHERE id_reset = :id_reset
            ")->execute([':id_reset' => $reset['id_reset']]);

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente. Redirigiendo al login...'
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error en restablecerConCodigo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al actualizar la contraseña.'];
        }
    }
}
