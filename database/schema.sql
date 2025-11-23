-- PeaceLink database schema generated from UML.
-- Run: mysql -u root -p < schema.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';

DROP DATABASE IF EXISTS peacelink;
CREATE DATABASE peacelink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE peacelink;

-- ===========================
-- Table: Utilisateur
-- ===========================
CREATE TABLE Utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    date_inscription DATE NOT NULL DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB;

-- ===========================
-- Table: Admin (inherits Utilisateur)
-- ===========================
CREATE TABLE Admin (
    id_utilisateur INT PRIMARY KEY,
    niveau_permission TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT fk_admin_user
        FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: Client (inherits Utilisateur)
-- ===========================
CREATE TABLE Client (
    id_utilisateur INT PRIMARY KEY,
    nom_complet VARCHAR(255) NOT NULL,
    bio TEXT NULL,
    CONSTRAINT fk_client_user
        FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: Organisation
-- ===========================
CREATE TABLE Organisation (
    id_utilisateur INT PRIMARY KEY,
    nom_organisation VARCHAR(255) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    statut_verification VARCHAR(50) NOT NULL DEFAULT 'pending',
    CONSTRAINT fk_organisation_user
        FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: Initiative
-- ===========================
CREATE TABLE Initiative (
    id_initiative INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'en_attente',
    date_evenement DATETIME NOT NULL,
    id_createur INT NOT NULL,
    CONSTRAINT fk_initiative_client
        FOREIGN KEY (id_createur) REFERENCES Client(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: Participation (client joins initiative)
-- ===========================
CREATE TABLE Participation (
    id_client INT NOT NULL,
    id_initiative INT NOT NULL,
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut VARCHAR(50) NOT NULL DEFAULT 'pending',
    PRIMARY KEY (id_client, id_initiative),
    CONSTRAINT fk_participation_client
        FOREIGN KEY (id_client) REFERENCES Client(id_utilisateur)
        ON DELETE CASCADE,
    CONSTRAINT fk_participation_initiative
        FOREIGN KEY (id_initiative) REFERENCES Initiative(id_initiative)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: Offre
-- ===========================
CREATE TABLE Offre (
    id_offre INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'draft',
    id_admin INT NOT NULL,
    CONSTRAINT fk_offre_admin
        FOREIGN KEY (id_admin) REFERENCES Admin(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: Candidature
-- ===========================
CREATE TABLE Candidature (
    id_candidature INT AUTO_INCREMENT PRIMARY KEY,
    motivation TEXT NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'pending',
    id_client INT NOT NULL,
    id_offre INT NOT NULL,
    CONSTRAINT fk_candidature_client
        FOREIGN KEY (id_client) REFERENCES Client(id_utilisateur)
        ON DELETE CASCADE,
    CONSTRAINT fk_candidature_offre
        FOREIGN KEY (id_offre) REFERENCES Offre(id_offre)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: Histoire
-- ===========================
CREATE TABLE Histoire (
    id_histoire INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'submitted',
    id_client INT NOT NULL,
    date_publication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_histoire_client
        FOREIGN KEY (id_client) REFERENCES Client(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: Commentaire
-- ===========================
CREATE TABLE Commentaire (
    id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    date_publication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    id_histoire INT NOT NULL,
    CONSTRAINT fk_commentaire_user
        FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
        ON DELETE CASCADE,
    CONSTRAINT fk_commentaire_histoire
        FOREIGN KEY (id_histoire) REFERENCES Histoire(id_histoire)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: Reclamation
-- ===========================
CREATE TABLE Reclamation (
    id_reclamation INT AUTO_INCREMENT PRIMARY KEY,
    description_personnalisee TEXT NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'nouvelle',
    id_auteur INT NOT NULL,
    id_histoire_cible INT NULL,
    id_commentaire_cible INT NULL,
    CONSTRAINT fk_reclamation_auteur
        FOREIGN KEY (id_auteur) REFERENCES Utilisateur(id_utilisateur)
        ON DELETE CASCADE,
    CONSTRAINT fk_reclamation_histoire
        FOREIGN KEY (id_histoire_cible) REFERENCES Histoire(id_histoire)
        ON DELETE SET NULL,
    CONSTRAINT fk_reclamation_commentaire
        FOREIGN KEY (id_commentaire_cible) REFERENCES Commentaire(id_commentaire)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ===========================
-- Table: Cause_Signalement
-- ===========================
CREATE TABLE Cause_Signalement (
    id_cause INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ===========================
-- Table: Reclamation_Cause (pivot)
-- ===========================
CREATE TABLE Reclamation_Cause (
    id_reclamation INT NOT NULL,
    id_cause INT NOT NULL,
    PRIMARY KEY (id_reclamation, id_cause),
    CONSTRAINT fk_rc_reclamation
        FOREIGN KEY (id_reclamation) REFERENCES Reclamation(id_reclamation)
        ON DELETE CASCADE,
    CONSTRAINT fk_rc_cause
        FOREIGN KEY (id_cause) REFERENCES Cause_Signalement(id_cause)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed common causes to speed up moderation.
INSERT INTO Cause_Signalement (libelle)
VALUES ('Discours haineux'), ('Spam'), ('Violation des règles'), ('Contenu sensible');

