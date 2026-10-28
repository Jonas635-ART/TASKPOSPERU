<?php

if (!defined('APP_INIT')) { http_response_code(403); exit(); }

class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;

        $envFile = __DIR__ . '/../.env';
        if (!file_exists($envFile)) {
            die("Error: Archivo de configuración .env no encontrado.");
        }
        
        $env = parse_ini_file($envFile);

        try {
            $dsn = "pgsql:host=" . $env['DB_HOST'] . ";port=" . $env['DB_PORT'] . ";dbname=" . $env['DB_NAME'];
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->conn = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], $options);
        } catch (PDOException $e) {
            error_log("Error de conexión BD: " . $e->getMessage());
            die("Error interno de conexión a la base de datos.");
        }

        return $this->conn;
    }
}