<?php

namespace Models;

use Core\Database;
use PDO;

class Like {

    public static function likeUser($from, $to): void {
        $db = Database::connect();

        // Evita curtidas duplicadas
        $stmt = $db->prepare(query: "SELECT * FROM curtidas WHERE quem_curtiu = ? AND quem_foi_curtido = ?");
        $stmt->execute(params: [$from, $to]);

        if ($stmt->rowCount() === 0) {
            $stmt = $db->prepare(query: "INSERT INTO curtidas (quem_curtiu, quem_foi_curtido, criado_em) VALUES (?, ?, NOW())");
            $stmt->execute(params: [$from, $to]);
        }
    }

    public static function isMatch($from, $to): bool {
        $db = Database::connect();

        $stmt = $db->prepare(query: "SELECT * FROM curtidas WHERE quem_curtiu = ? AND quem_foi_curtido = ?");
        $stmt->execute(params: [$to, $from]);

        return $stmt->rowCount() > 0;
    }

    public static function getMatches($userId): array {
        $db = Database::connect();

        $stmt = $db->prepare(query: "
            SELECT u.id, u.nome
            FROM usuarios u
            JOIN curtidas l1 ON l1.quem_foi_curtido = u.id AND l1.quem_curtiu = ?
            JOIN curtidas l2 ON l2.quem_curtiu = u.id AND l2.quem_foi_curtido = ?
        ");
        $stmt->execute(params: [$userId, $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function hasLiked($from, $to): bool {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT 1 FROM curtidas WHERE quem_curtiu = ? AND quem_foi_curtido = ?");
        $stmt->execute([$from, $to]);
        return $stmt->rowCount() > 0;
    }
    
    public static function getSugestoes($userId): array {
        $db = Database::connect();
    
        // Primeiro, buscamos os dados do usuário atual
        $stmt = $db->prepare("SELECT genero, orientacao, genero_interesse FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$usuario) {
            return [];
        }
    
        $genero = $usuario['genero'];
        $orientacao = $usuario['orientacao'];
        $genero_interesse = $usuario['genero_interesse'];
    
        // Baseado na orientação, definimos os gêneros de interesse
        $generosAlvo = [];
    
        if ($orientacao === 'hetero') {
            if ($genero === 'masculino') $generosAlvo[] = 'feminino';
            elseif ($genero === 'feminino') $generosAlvo[] = 'masculino';
            // Outros casos podem ser tratados conforme sua lógica
        } elseif ($orientacao === 'Gay') {
            if ($genero === 'masculino') $generosAlvo[] = 'masculino';
        } elseif ($orientacao === 'lesbica') {
            if ($genero === 'feminino') $generosAlvo[] = 'feminino';
        } elseif (in_array($orientacao, ['Bisexual', 'Pansexual', 'Outro'])) {
            // Usa diretamente o campo genero_interesse
            $generosAlvo[] = $genero_interesse;
        }
    
        // Garante que há pelo menos um gênero de interesse
        if (empty($generosAlvo)) {
            return [];
        }
    
        // Monta placeholders para os gêneros
        $placeholders = implode(',', array_fill(0, count($generosAlvo), '?'));
    
        // Monta a query considerando a identidade de gênero
        $query = "
            SELECT id, nome
            FROM usuarios
            WHERE id != ?
            AND genero IN ($placeholders)
            AND id NOT IN (
                SELECT quem_foi_curtido FROM curtidas WHERE quem_curtiu = ?
            )
        ";
    
        $stmt = $db->prepare($query);
        $params = array_merge([$userId], $generosAlvo, [$userId]);
        $stmt->execute($params);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public static function match($user1, $user2): void {
        $db = Database::connect();
    
        // Ordena os IDs para evitar duplicações
        $usuario1 = min($user1, $user2);
        $usuario2 = max($user1, $user2);
    
        // Verifica se o match já foi registrado
        $stmt = $db->prepare("
            SELECT 1 FROM matches
            WHERE usuario1_id = ? AND usuario2_id = ?
        ");
        $stmt->execute([$usuario1, $usuario2]);
    
        if ($stmt->rowCount() === 0) {
            // Insere novo match
            $stmt = $db->prepare("
                INSERT INTO matches (usuario1_id, usuario2_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$usuario1, $usuario2]);
        }
    }

    public static function getUserMatches($userId): array {
        $db = Database::connect();
    
        $stmt = $db->prepare("
            SELECT u.id, u.nome
            FROM matches m
            JOIN usuarios u ON (u.id = m.usuario1_id OR u.id = m.usuario2_id)
            WHERE (? IN (m.usuario1_id, m.usuario2_id)) AND u.id != ?
        ");
        $stmt->execute([$userId, $userId]);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    
}


