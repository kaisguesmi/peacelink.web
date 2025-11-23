<?php

/**
 * Modèle Admin : interface avec la table Admin.
 */
class Admin extends Model
{
    protected string $table = 'Admin';
    protected string $primaryKey = 'id_utilisateur';

    public function isAdmin(int $userId): bool
    {
        return (bool) $this->findById($userId);
    }
}

