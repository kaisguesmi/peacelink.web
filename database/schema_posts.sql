-- Additional tables for social media posts system
-- Run this after the main schema.sql

USE peacelink;

-- ===========================
-- Table: Post (replaces Histoire for social media style)
-- ===========================
CREATE TABLE Post (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_post_user
        FOREIGN KEY (user_id) REFERENCES Utilisateur(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: Reaction (for post reactions)
-- ===========================
CREATE TABLE Reaction (
    id_reaction INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'like', -- like, love, laugh, angry
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_post_reaction (post_id, user_id, type),
    CONSTRAINT fk_reaction_post
        FOREIGN KEY (post_id) REFERENCES Post(id_post)
        ON DELETE CASCADE,
    CONSTRAINT fk_reaction_user
        FOREIGN KEY (user_id) REFERENCES Utilisateur(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- Table: PostComment (for comments on posts)
-- ===========================
CREATE TABLE PostComment (
    id_comment INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_postcomment_post
        FOREIGN KEY (post_id) REFERENCES Post(id_post)
        ON DELETE CASCADE,
    CONSTRAINT fk_postcomment_user
        FOREIGN KEY (user_id) REFERENCES Utilisateur(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Add avatar column to Client (if it doesn't exist, you may need to check manually)
-- ALTER TABLE Client ADD COLUMN avatar VARCHAR(255) NULL;

