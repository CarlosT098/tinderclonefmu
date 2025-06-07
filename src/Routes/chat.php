<?php
require_once '../controllers/ChatController.php';

$chat = new ChatController();

// Obter método da requisição
$method = $_SERVER['REQUEST_METHOD'];

// Roteamento básico
switch($method) {
    case 'GET':
        if(isset($_GET['match_id'])) {
            $chat->getMessages($_GET['match_id']);
        } else {
            $chat->getChatList();
        }
        break;
    case 'POST':
        $chat->sendMessage();
        break;
    default:
        http_response_code(405);
        echo json_encode(array('message' => 'Método não permitido'));
        break;
}
?>