<?php

if (!defined('APP_INIT')) { http_response_code(403); exit(); }

class Database {
    private static ?PDO $pdoInstance = null;
    private static ?array $envCache  = null;
    public ?PDO $conn                = null;

    public static function getEnv(): array {
        if (self::$envCache === null) {
            $envFile = __DIR__ . '/../.env';
            if (!file_exists($envFile)) {
                die("Error: Archivo de configuración .env no encontrado.");
            }
            $parsed = parse_ini_file($envFile);
            self::$envCache = is_array($parsed) ? $parsed : [];
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
            $dsn = "pgsql:host=" . $env['DB_HOST'] . ";port=" . $env['DB_PORT'] . ";dbname=" . $env['DB_NAME'];
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
