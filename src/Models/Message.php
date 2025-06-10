<?php
class Message {
    private $conn;
    private $table = 'messages';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getChatList($userId) {
        $query = "SELECT m.match_id, 
                         MAX(m.created_at) as last_message_time,
                         SUM(CASE WHEN m.receiver_id = :user_id AND m.read_status = 0 THEN 1 ELSE 0 END) as unread_count,
                         u.id as partner_id,
                         u.name as partner_name,
                         u.profile_picture as partner_picture
                  FROM {$this->table} m
                  JOIN users u ON (u.id = CASE 
                                          WHEN m.sender_id = :user_id THEN m.receiver_id 
                                          ELSE m.sender_id 
                                        END)
                  WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                  GROUP BY m.match_id, u.id, u.name, u.profile_picture
                  ORDER BY last_message_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByMatch($matchId, $userId) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE match_id = :match_id
                  ORDER BY created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':match_id', $matchId);
        $stmt->execute();

        $this->markAsRead($matchId, $userId);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (match_id, sender_id, receiver_id, content, read_status)
                  VALUES (:match_id, :sender_id, :receiver_id, :content, 0)";

        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            ':match_id' => $data['match_id'],
            ':sender_id' => $data['sender_id'],
            ':receiver_id' => $data['receiver_id'],
            ':content' => $data['content']
        ]);
    }

    public function markAsRead($matchId, $userId) {
        $query = "UPDATE {$this->table} 
                  SET read_status = 1
                  WHERE match_id = :match_id 
                  AND receiver_id = :user_id
                  AND read_status = 0";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':match_id' => $matchId,
            ':user_id' => $userId
        ]);
    }
}