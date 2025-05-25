<?php

namespace Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper {
    private static $secret;

    private static function loadEnv(){
        if (!self::$secret) {
            $env = parse_ini_file(dirname(__DIR__, 2) . '/db-credentials.env');
            self::$secret = $env['JWT_SECRET'] ?? 'default_secret';
        }
    }

    public static function generateToken($userID){
        self::loadEnv();
        $payload = [
            'iss' => 'localhost',
            'aud' => 'localhost',
            'iat' => time(),
            'exp' => time() + 3600,
            'user_id' => $userID
        ];

        return JWT::encode($payload, self::$secret, 'HS256');
    }

    public static function validateToken($token){
        self::loadEnv();

        try {
            return JWT::decode($token, new Key(self::$secret,'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}