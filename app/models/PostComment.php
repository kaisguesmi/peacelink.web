<?php

/**
 * Modèle PostComment : gestion des commentaires sur les posts.
 */
class PostComment extends Model
{
    protected string $table = 'PostComment';
    protected string $primaryKey = 'id_comment';

    public function getByPost(int $postId): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $sql = "SELECT pc.*, 
                c.nom_complet, 
                {$avatarField},
                u.email
                FROM PostComment pc
                JOIN Utilisateur u ON u.id_utilisateur = pc.user_id
                LEFT JOIN Client c ON c.id_utilisateur = pc.user_id
                WHERE pc.post_id = :post_id
                ORDER BY pc.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll();
    }

    public function getAllWithUsers(): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $sql = "SELECT pc.*, 
                c.nom_complet, 
                {$avatarField},
                u.email
                FROM PostComment pc
                JOIN Utilisateur u ON u.id_utilisateur = pc.user_id
                LEFT JOIN Client c ON c.id_utilisateur = pc.user_id
                ORDER BY pc.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }
}

