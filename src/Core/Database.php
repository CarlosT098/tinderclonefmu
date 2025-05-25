<?php

namespace Core;

use PDO;
use PDOException;

class Database {
    private static $pdo;

    public static function connect() {
        if (!self::$pdo) {
            $env = parse_ini_file(dirname(__DIR__, 2) . '/db-credentials.env');
            try {
                self::$pdo = new PDO(
                    dsn: "pgsql:host={$env['PGHOST']};dbname={$env['PGDATABASE']}",
                    username: $env['PGUSER'],
                    password: $env['PGPASSWORD']
                );
                self::$pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Erro na conexão: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
