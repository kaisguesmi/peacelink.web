<?php

/**
 * Modèle Post : gestion des posts de type réseau social.
 */
class Post extends Model
{
    protected string $table = 'Post';
    protected string $primaryKey = 'id_post';

    public function getAllWithUsers(bool $includePending = false): array
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
                WHERE 1=1";
                
        if (!$includePending) {
            $sql .= " AND p.status = 'approved'";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
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

    public function getByUserId(int $userId, bool $includePending = false): array
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
                WHERE p.user_id = :user_id";
                
        // When including pending, show approved + pending but never rejected
        if ($includePending) {
            $sql .= " AND p.status IN ('approved', 'pending')";
        } else {
            $sql .= " AND p.status = 'approved'";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get posts pending moderation
     */
    public function getPendingPosts(): array
    {
        return $this->findAll('status = ?', ['pending']);
    }
    
    /**
     * Approve a post
     */
    public function approvePost(int $postId, ?string $notes = null): bool
    {
        return $this->update($postId, [
            'status' => 'approved',
            'moderation_notes' => $notes
        ]);
    }
    
    /**
     * Reject a post
     */
    public function rejectPost(int $postId, string $reason): bool
    {
        return $this->update($postId, [
            'status' => 'rejected',
            'moderation_notes' => $reason
        ]);
    }

    /**
     * Get latest approved posts for the public Stories section (limited)
     */
    public function getLatestPublicStories(int $limit = 6): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarExpr = $checkColumn ? 'c.avatar' : 'NULL';
        } catch (Exception $e) {
            $avatarExpr = 'NULL';
        }

        $sql = "SELECT 
                    p.id_post AS id,
                    p.title AS titre,
                    p.content AS contenu,
                    p.created_at AS date_publication,
                    COALESCE(c.nom_complet, u.email) AS nom_complet,
                    " . $avatarExpr . " AS photo_profil
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.user_id
                LEFT JOIN Client c ON c.id_utilisateur = p.user_id
                WHERE p.status = 'approved'
                ORDER BY p.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all approved posts for the public Stories listing
     */
    public function getAllPublicStories(): array
    {
        // Check if avatar column exists
        try {
            $checkColumn = $this->db->query("SHOW COLUMNS FROM Client LIKE 'avatar'")->fetch();
            $avatarExpr = $checkColumn ? 'c.avatar' : 'NULL';
        } catch (Exception $e) {
            $avatarExpr = 'NULL';
        }

        $sql = "SELECT 
                    p.id_post AS id,
                    p.title AS titre,
                    p.content AS contenu,
                    p.created_at AS date_publication,
                    COALESCE(c.nom_complet, u.email) AS nom_complet,
                    " . $avatarExpr . " AS photo_profil
                FROM Post p
                JOIN Utilisateur u ON u.id_utilisateur = p.user_id
                LEFT JOIN Client c ON c.id_utilisateur = p.user_id
                WHERE p.status = 'approved'
                ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

