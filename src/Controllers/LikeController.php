<?php
namespace Controllers;
class LikeController {
    public function likeUser() {
        session_start();
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'] ?? null;
        $targetId = $_POST['user_id'] ?? null;

        if (!$userId || !$targetId) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados insuficientes.']);
            return;
        }

        try {
            $likeJaExiste = \Models\Like::hasLiked($userId, $targetId);

            if ($likeJaExiste) {
                echo json_encode(['status' => 'like já efetuado']);
                return;
            }

            \Models\Like::likeUser($userId, $targetId);

            if (\Models\Like::isMatch($userId, $targetId)) {
                \Models\Like::match($userId, $targetId); // Exemplo de função match, que você pode definir
                echo json_encode(['status' => 'match']);
                return;
            }

            echo json_encode(['status' => 'like registrado']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erro ao processar o like.',
                'exception' => $e->getMessage()
            ]);
        }
    }

    public function likeList() {
        session_start();
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Usuário não autenticado']);
            return;
        }

        $sugestoes = \Models\Like::getSugestoes($userId);
        echo json_encode($sugestoes);
    }
}
