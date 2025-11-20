<?php

/**
 * Modèle Histoire : opérations CRUD + agrégations.
 */
class Histoire extends Model
{
    protected string $table = 'Histoire';
    protected string $primaryKey = 'id_histoire';

    public function getWithClients(): array
    {
        $sql = "SELECT h.*, c.nom_complet 
                FROM Histoire h
                JOIN Client c ON c.id_utilisateur = h.id_client
                ORDER BY h.date_publication DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getWithComments(int $storyId): ?array
    {
        $story = $this->findById($storyId);
        if (!$story) {
            return null;
        }

        $commentModel = new Commentaire();
        $story['commentaires'] = $commentModel->getByStory($storyId);
        return $story;
    }

    /**
     * Les réactions sont implémentées sous forme de commentaires courts (emoji/mot-clé).
     */
    public function react(int $storyId, int $userId, string $emoji): bool
    {
        $reaction = [
            'contenu' => "[reaction] {$emoji}",
            'id_utilisateur' => $userId,
            'id_histoire' => $storyId,
        ];

        $commentModel = new Commentaire();
        return (bool) $commentModel->create($reaction);
    }

    public function moderate(int $id, string $status): bool
    {
        return $this->update($id, ['statut' => $status]);
    }
}

