<?php

class Commentaire extends Model
{
    protected string $table = 'Commentaire';
    protected string $primaryKey = 'id_commentaire';

    public function getByStory(int $storyId): array
    {
        $sql = "SELECT com.*, u.email 
                FROM Commentaire com
                JOIN Utilisateur u ON u.id_utilisateur = com.id_utilisateur
                WHERE com.id_histoire = :id
                ORDER BY com.date_publication DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $storyId]);
        return $stmt->fetchAll();
    }

    public function getAllWithUsers(): array
    {
        $sql = "SELECT com.*, u.email 
                FROM Commentaire com
                JOIN Utilisateur u ON u.id_utilisateur = com.id_utilisateur
                ORDER BY com.date_publication DESC";
        return $this->db->query($sql)->fetchAll();
    }
}

