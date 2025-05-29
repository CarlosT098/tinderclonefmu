<?php

namespace Models;

use Core\Database;
use PDO;

class User {
    // Encontra usuário pelo email
    public static function findByEmail($email): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Cria um novo usuário
    public static function create($data) {
        $db = Database::connect();
    
        $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, genero, orientacao, nascimento) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['nome'],
            $data['email'],
            password_hash($data['senha'], PASSWORD_DEFAULT),
            $data['genero'],
            $data['orientacao'],
            $data['nascimento']
        ]);
    }

    // Gera o token de recuperação de senha e armazena no banco
    public static function generateResetToken($email) {
        $db = Database::connect();
        
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
    
        if ($stmt->rowCount() === 0) return false;
    
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $token = bin2hex(random_bytes(16)); // Gera o token
    
        // Insere o token de reset na tabela password_resets
        $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, NOW() + INTERVAL '1 hour')");
        $stmt->execute([$user['id'], $token]);
    
        return $token;
    }

    // Verifica se o token de recuperação é válido
    public static function findByResetToken($token) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT u.id, u.email FROM usuarios u JOIN password_resets pr ON u.id = pr.user_id WHERE pr.token = ? AND pr.expires_at > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Atualiza a senha do usuário
    public static function updatePassword($userId, $newPassword) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        return $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
    }

    // Gera um token de verificação de email
    public static function generateVerificationToken($userId) {
        $token = bin2hex(random_bytes(16)); // Gera o token de verificação
        $db = Database::connect();
        
        $stmt = $db->prepare("UPDATE usuarios SET token = ? WHERE id = ?");
        $stmt->execute([$token, $userId]);
        
        return $token;
    }

    // Verifica o token de verificação de email
    public static function verifyEmail($token) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE token = ?");
        $stmt->execute([$token]);
        
        if ($stmt->rowCount() === 0) {
            return false;
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $db->prepare("UPDATE usuarios SET ativo = TRUE, token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return true;
    }

    // Encontra o usuário pelo ID
    public static function findById($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Atualiza os dados do perfil do usuário
    public static function updateProfile($userId, $data) {
        $db = Database::connect();

        // Atualiza os dados do perfil (como nome, bio, etc.)
        $stmt = $db->prepare("UPDATE usuarios SET nome = ?, bio = ?, genero_interesse = ?, gostos = ?, nascimento = ?, idade = ? WHERE id = ?");
        return $stmt->execute([
            $data['nome'],
            $data['bio'],
            $data['genero_interesse'],
            json_encode($data['gostos']),
            $data['nascimento'],
            $data['idade'],
            $userId
        ]);
    }

    // Método para gerar o JWT com a ID do usuário
    public static function generateJwtToken($userId) {
        // Use a biblioteca JWT para gerar o token (firebase/php-jwt)
        $key = 'SUA_CHAVE_SECRETA';
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;  // Expira em 1 hora
        $payload = [
            'iss' => 'seusite.com',
            'aud' => 'seusite.com',
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $userId
        ];
        return \Firebase\JWT\JWT::encode($payload, $key);
    }

    //função para atualizar a imagem 
    public function updateProfilePicture($userId, $path)
{
    $db = Database::connect();
    $stmt = $db->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    return $stmt->execute([$path, $userId]);
}

}
