<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/Database.php';
require_once '../models/Message.php';

class ChatController {
    private $db;
    private $message;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->message = new Message($this->db);
    }

    // Enviar mensagem
    public function sendMessage() {
        $data = json_decode(file_get_contents("php://input"));

        // Verificar token JWT (implementar conforme sua autenticação)
        // $this->verifyToken();

        $this->message->match_id = $data->match_id;
        $this->message->sender_id = $data->sender_id;
        $this->message->receiver_id = $data->receiver_id;
        $this->message->content = $data->content;

        if($this->message->create()) {
            echo json_encode(
                array('message' => 'Mensagem enviada com sucesso')
            );
        } else {
            echo json_encode(
                array('message' => 'Erro ao enviar mensagem')
            );
        }
    }

    // Obter mensagens de um match
    public function getMessages($match_id) {
        // Verificar token JWT e obter user_id
        // $user_id = $this->verifyTokenAndGetUserId();
        $user_id = 1; // Temporário para teste

        $stmt = $this->message->getByMatch($match_id, $user_id);
        $num = $stmt->rowCount();

        if($num > 0) {
            $messages_arr = array();
            $messages_arr['data'] = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $message_item = array(
                    'id' => $id,
                    'sender_id' => $sender_id,
                    'content' => $content,
                    'read_status' => $read_status,
                    'created_at' => $created_at
                );

                array_push($messages_arr['data'], $message_item);
            }

            echo json_encode($messages_arr);
        } else {
            echo json_encode(
                array('message' => 'Nenhuma mensagem encontrada')
            );
        }
    }

    // Obter lista de chats
    public function getChatList() {
        // Verificar token JWT e obter user_id
        // $user_id = $this->verifyTokenAndGetUserId();
        $user_id = 1; // Temporário para teste

        $stmt = $this->message->getChatList($user_id);
        $num = $stmt->rowCount();

        if($num > 0) {
            $chats_arr = array();
            $chats_arr['data'] = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $chat_item = array(
                    'match_id' => $match_id,
                    'last_message_time' => $last_message_time,
                    'unread_count' => $unread_count,
                    'partner_name' => $partner_name,
                    'partner_picture' => $partner_picture
                );

                array_push($chats_arr['data'], $chat_item);
            }

            echo json_encode($chats_arr);
        } else {
            echo json_encode(
                array('message' => 'Nenhum chat encontrado')
            );
        }
    }
}
?>