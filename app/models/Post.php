<?php

/**
 * Modèle Post : gestion des posts de type réseau social.
 */
class Post extends Model
{
    protected string $table = 'Post';
    protected string $primaryKey = 'id_post';

    public function getAllWithUsers(): array
    {
        // Check if avatar column exists, if not use NULL
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $sql = "SELECT p.*, 
                c.nom_complet, 
                {$avatarField},
                u.email,
                (SELECT COUNT(*) FROM Reaction r WHERE r.post_id = p.id_post) as reaction_count,
                (SELECT COUNT(*) FROM PostComment pc WHERE pc.post_id = p.id_post) as comment_count
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.user_id
                LEFT JOIN Client c ON c.id_utilisateur = p.user_id
                ORDER BY p.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getByIdWithUser(int $postId): ?array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $sql = "SELECT p.*, 
                c.nom_complet, 
                {$avatarField},
                u.email,
                (SELECT COUNT(*) FROM Reaction r WHERE r.post_id = p.id_post) as reaction_count,
                (SELECT COUNT(*) FROM PostComment pc WHERE pc.post_id = p.id_post) as comment_count
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.user_id
                LEFT JOIN Client c ON c.id_utilisateur = p.user_id
                WHERE p.id_post = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $postId]);
        $post = $stmt->fetch();
        return $post ?: null;
    }

    public function getByUserId(int $userId): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarField = $checkColumn ? 'c.avatar' : 'NULL as avatar';
        } catch (Exception $e) {
            $avatarField = 'NULL as avatar';
        }
        
        $sql = "SELECT p.*, 
                c.nom_complet, 
                {$avatarField},
                u.email,
                (SELECT COUNT(*) FROM Reaction r WHERE r.post_id = p.id_post) as reaction_count,
                (SELECT COUNT(*) FROM PostComment pc WHERE pc.post_id = p.id_post) as comment_count
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.user_id
                LEFT JOIN Client c ON c.id_utilisateur = p.user_id
                WHERE p.user_id = :user_id
                ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}

