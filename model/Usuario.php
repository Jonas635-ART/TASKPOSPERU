<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
require_once __DIR__ . '/../config/conexion.php';

class Usuario {
    private $pdo;
    private const MAX_INTENTOS_CODIGO = 5;
    private const MINUTOS_VALIDEZ     = 30;

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
                ':rol'      => $rol,
            ]);

            return ['success' => true, 'message' => 'Usuario registrado exitosamente.'];
        } catch (PDOException $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al procesar el registro en el servidor.'];
        }
    }

    // ==========================================================
    // Recuperación de contraseña asistida por el Administrador
    // ==========================================================

    // 1. El usuario solicita restablecer su contraseña (solo con su correo)
    public function crearSolicitudReset(string $correo): bool {
        try {
            $usuario = $this->obtenerPorCorreo($correo);
            if (!$usuario) {
                // Devolvemos true igualmente para no revelar si el correo existe
                return true;
            }

            // Si ya tiene una solicitud abierta ('pendiente' o 'codigo_generado'), actualizamos la fecha
            $stmt = $this->pdo->prepare("
                SELECT id_reset FROM password_resets
                WHERE id_usuario = :id AND estado IN ('pendiente', 'codigo_generado')
                LIMIT 1
            ");
            $stmt->execute([':id' => $usuario['id_usuario']]);
            $existente = $stmt->fetchColumn();

            if ($existente) {
                $upd = $this->pdo->prepare("
                    UPDATE password_resets
                    SET fecha_solicitud = CURRENT_TIMESTAMP
                    WHERE id_reset = :id_reset
                ");
                return $upd->execute([':id_reset' => $existente]);
            }

            $ins = $this->pdo->prepare("
                INSERT INTO password_resets (id_usuario, estado)
                VALUES (:id, 'pendiente')
            ");
            return $ins->execute([':id' => $usuario['id_usuario']]);
        } catch (PDOException $e) {
            error_log("Error en crearSolicitudReset: " . $e->getMessage());
            return false;
        }
    }

    // 2. El Admin genera un código temporal de un solo uso (ej. POS-4829-K7) para un usuario
    public function generarCodigoTemporalAdmin(int $id_usuario, int $id_admin): ?array {
        try {
            $stmtUser = $this->pdo->prepare("SELECT id_usuario, nombre, correo FROM usuarios WHERE id_usuario = :id LIMIT 1");
            $stmtUser->execute([':id' => $id_usuario]);
            $usuario = $stmtUser->fetch();
            if (!$usuario) {
                return null;
            }

            // Generar código legible: POS-XXXX-XX
            $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $bloque1 = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $bloque2 = $caracteres[random_int(0, strlen($caracteres) - 1)] . $caracteres[random_int(0, strlen($caracteres) - 1)];
            $codigoPlano = "POS-{$bloque1}-{$bloque2}";
            $codigoHash  = password_hash($codigoPlano, PASSWORD_BCRYPT);

            $this->pdo->beginTransaction();

            // Invalidar solicitudes previas abiertas de ese usuario
            $this->pdo->prepare("
                DELETE FROM password_resets
                WHERE id_usuario = :id AND estado IN ('pendiente', 'codigo_generado')
            ")->execute([':id' => $id_usuario]);

            // Crear el registro con el código activo por 30 minutos
            $sql = "INSERT INTO password_resets (id_usuario, estado, codigo_hash, expira_en, intentos, id_admin)
                    VALUES (:id_usr, 'codigo_generado', :hash, NOW() + INTERVAL '" . self::MINUTOS_VALIDEZ . " minutes', 0, :id_admin)";
            $this->pdo->prepare($sql)->execute([
                ':id_usr'   => $id_usuario,
                ':hash'     => $codigoHash,
                ':id_admin' => $id_admin,
            ]);

            $this->pdo->commit();

            return [
                'codigo'  => $codigoPlano,
                'minutos' => self::MINUTOS_VALIDEZ,
                'usuario' => $usuario,
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error en generarCodigoTemporalAdmin: " . $e->getMessage());
            return null;
        }
    }

    // 3. El usuario canjea el código temporal que le dio el Admin y define su nueva contraseña
    public function restablecerConCodigoAdmin(string $correo, string $codigo, string $nuevaPassword): array {
        try {
            $usuario = $this->obtenerPorCorreo($correo);
            if (!$usuario) {
                return ['success' => false, 'message' => 'Correo o código de autorización inválido o expirado.'];
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
                return ['success' => false, 'message' => 'No hay un código activo para esta cuenta. Solicita uno al Administrador.'];
            }

            if (empty($reset['vigente'])) {
                $this->pdo->prepare("DELETE FROM password_resets WHERE id_reset = :id")->execute([':id' => $reset['id_reset']]);
                return ['success' => false, 'message' => 'El código temporal ha expirado (validez de 30 min). Solicita uno nuevo al Administrador.'];
            }

            $codigoNormalizado = strtoupper(trim($codigo));
            if (!password_verify($codigoNormalizado, $reset['codigo_hash'])) {
                $intentos = ((int)$reset['intentos']) + 1;
                if ($intentos >= self::MAX_INTENTOS_CODIGO) {
                    $this->pdo->prepare("DELETE FROM password_resets WHERE id_reset = :id")->execute([':id' => $reset['id_reset']]);
                    return ['success' => false, 'message' => 'Demasiados intentos fallidos. El código fue anulado por seguridad; solicita otro al Administrador.'];
                }

                $this->pdo->prepare("UPDATE password_resets SET intentos = :i WHERE id_reset = :id")
                          ->execute([':i' => $intentos, ':id' => $reset['id_reset']]);
                $restantes = self::MAX_INTENTOS_CODIGO - $intentos;
                return ['success' => false, 'message' => "Código incorrecto ({$restantes} intentos restantes)."];
            }

            // Código válido: actualizar contraseña y marcar solicitud como completada
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
            error_log("Error en restablecerConCodigoAdmin: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al actualizar la contraseña.'];
        }
    }

    // 4. Listar todos los usuarios junto con el estado de su solicitud de restablecimiento (para el panel del Admin)
    public function listarUsuariosConEstadoReset(): array {
        try {
            $sql = "SELECT u.id_usuario, u.nombre, u.correo, u.rol,
                           pr.id_reset, pr.estado AS estado_reset, pr.fecha_solicitud, pr.expira_en,
                           (pr.expira_en IS NOT NULL AND pr.expira_en > NOW()) AS codigo_vigente
                    FROM usuarios u
                    LEFT JOIN LATERAL (
                        SELECT id_reset, estado, fecha_solicitud, expira_en
                        FROM password_resets p
                        WHERE p.id_usuario = u.id_usuario
                          AND p.estado IN ('pendiente', 'codigo_generado')
                        ORDER BY p.fecha_solicitud DESC
                        LIMIT 1
                    ) pr ON TRUE
                    ORDER BY
                        CASE
                            WHEN pr.estado = 'pendiente' THEN 0
                            WHEN pr.estado = 'codigo_generado' THEN 1
                            ELSE 2
                        END,
                        pr.fecha_solicitud DESC NULLS LAST,
                        u.nombre ASC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarUsuariosConEstadoReset: " . $e->getMessage());
            return [];
        }
    }
}
?>
