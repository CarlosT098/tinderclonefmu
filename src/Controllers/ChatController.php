<?php
namespace Controllers;

use Helpers\JwtHelper;
use Models\Messages;
use Core\Database;

class ChatController {
    private $messageModel;
    private $auth;

    public function __construct() {
        $database = new Database();
        $db = $database->connect();
        $this->messageModel = new Message($db);
        $this->auth = new AuthMiddleware();
    }

    public function list() {
        try {
            $userId = $this->auth->getUserId();
            
            $chats = $this->messageModel->getChatList($userId);
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $chats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function messages($matchId) {
        try {
            $userId = $this->auth->getUserId();
            
            $messages = $this->messageModel->getByMatch($matchId, $userId);
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $messages
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function send() {
        try {
            $userId = $this->auth->getUserId();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['match_id']) || empty($data['receiver_id']) || empty($data['content'])) {
                throw new Exception('Dados incompletos para enviar mensagem');
            }
            
            $result = $this->messageModel->create([
                'match_id' => $data['match_id'],
                'sender_id' => $userId,
                'receiver_id' => $data['receiver_id'],
                'content' => $data['content']
            ]);
            
            if ($result) {
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Mensagem enviada com sucesso'
                ]);
            } else {
                throw new Exception('Falha ao enviar mensagem');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function markAsRead($matchId) {
        try {
            $userId = $this->auth->getUserId();
            
            $success = $this->messageModel->markAsRead($matchId, $userId);
            
            if ($success) {
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Mensagens marcadas como lidas'
                ]);
            } else {
                throw new Exception('Nenhuma mensagem para marcar como lida');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}