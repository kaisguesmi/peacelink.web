-- Create notifications table for user alerts (posts, comments, stories, etc.)
USE peacelink;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(190) NOT NULL DEFAULT 'Notification',
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `read` TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES Utilisateur(id_utilisateur)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_notifications_user_read ON notifications(user_id, `read`);
