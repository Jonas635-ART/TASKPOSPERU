<?php

if (!defined('APP_INIT')) { http_response_code(403); exit(); }

class Database {
    private static ?PDO $pdoInstance = null;
    private static ?array $envCache  = null;
    public ?PDO $conn                = null;

    public static function getEnv(): array {
        if (self::$envCache === null) {
            $envFile = __DIR__ . '/../.env';
            $parsed  = [];

            //  .env (entorno local XAMPP)
            if (file_exists($envFile)) {
                $ini    = parse_ini_file($envFile);
                $parsed = is_array($ini) ? $ini : [];
            }

            //  archivo .env -> variables de entorno de Railway
            $keys = [
                'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS',
                'TOKEN_DEV', 'TOKEN_ADMIN',
                'SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'SMTP_FROM', 'SMTP_FROM_NAME'
            ];

            foreach ($keys as $key) {
                if (!isset($parsed[$key]) || $parsed[$key] === '') {
                    $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
                    if ($val !== false && $val !== null && $val !== '') {
                        $parsed[$key] = $val;
                    }
                }
            }

            if (empty($parsed['DB_HOST']) || empty($parsed['DB_NAME'])) {
                die("Error: Configuración de entorno no encontrada.");
            }

            self::$envCache = $parsed;
        }
        return self::$envCache;
    }

    public function getConnection(): PDO {
        if (self::$pdoInstance !== null) {
            $this->conn = self::$pdoInstance;
            return self::$pdoInstance;
        }

        $env = self::getEnv();

        try {
            $dsn = "pgsql:host=" . $env['DB_HOST'] . ";port=" . ($env['DB_PORT'] ?? '5432') . ";dbname=" . $env['DB_NAME'];
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$pdoInstance = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], $options);
            $this->conn = self::$pdoInstance;
        } catch (PDOException $e) {
            error_log("Error de conexión BD: " . $e->getMessage());
            die("Error interno de conexión a la base de datos.");
        }

        return self::$pdoInstance;
    }
}
