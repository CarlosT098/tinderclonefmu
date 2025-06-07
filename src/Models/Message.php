<?php
class Message {
    private $conn;
    private $table = 'messages';

    public $id;
    public $match_id;
    public $sender_id;
    public $receiver_id;
    public $content;
    public $read_status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar nova mensagem
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET match_id = :match_id,
                      sender_id = :sender_id,
                      receiver_id = :receiver_id,
                      content = :content,
                      read_status = 0,
                      created_at = NOW()';

        $stmt = $this->conn->prepare($query);

        // Limpar dados
        $this->match_id = htmlspecialchars(strip_tags($this->match_id));
        $this->sender_id = htmlspecialchars(strip_tags($this->sender_id));
        $this->receiver_id = htmlspecialchars(strip_tags($this->receiver_id));
        $this->content = htmlspecialchars(strip_tags($this->content));

        // Vincular parâmetros
        $stmt->bindParam(':match_id', $this->match_id);
        $stmt->bindParam(':sender_id', $this->sender_id);
        $stmt->bindParam(':receiver_id', $this->receiver_id);
        $stmt->bindParam(':content', $this->content);

        if($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Obter mensagens por match
    public function getByMatch($match_id, $user_id) {
        $query = 'SELECT * FROM ' . $this->table . ' 
                  WHERE match_id = :match_id
                  ORDER BY created_at ASC';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':match_id', $match_id);
        $stmt->execute();

        // Marcar mensagens como lidas
        $this->markAsRead($match_id, $user_id);

        return $stmt;
    }

    // Marcar mensagens como lidas
    private function markAsRead($match_id, $user_id) {
        $query = 'UPDATE ' . $this->table . ' 
                  SET read_status = 1
                  WHERE match_id = :match_id 
                  AND receiver_id = :user_id
                  AND read_status = 0';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':match_id', $match_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }

    // Obter lista de chats
    public function getChatList($user_id) {
        $query = 'SELECT m.match_id, 
                         MAX(m.created_at) as last_message_time,
                         SUM(CASE WHEN m.receiver_id = :user_id AND m.read_status = 0 THEN 1 ELSE 0 END) as unread_count,
                         u.name as partner_name,
                         u.profile_picture as partner_picture
                  FROM ' . $this->table . ' m
                  JOIN users u ON (u.id = CASE 
                                          WHEN m.sender_id = :user_id THEN m.receiver_id 
                                          ELSE m.sender_id 
                                        END)
                  WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                  GROUP BY m.match_id, partner_name, partner_picture
                  ORDER BY last_message_time DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt;
    }
}
?>