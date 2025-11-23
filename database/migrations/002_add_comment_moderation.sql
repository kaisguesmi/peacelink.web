-- Add status column to PostComment table for moderation
ALTER TABLE PostComment 
ADD COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending';

-- Add moderation fields
ALTER TABLE PostComment
ADD COLUMN moderation_notes TEXT NULL AFTER status,
ADD COLUMN moderated_at DATETIME NULL AFTER moderation_notes,
ADD COLUMN moderated_by INT NULL AFTER moderated_at,
ADD CONSTRAINT fk_comment_moderator
    FOREIGN KEY (moderated_by) REFERENCES Utilisateur(id_utilisateur)
    ON DELETE SET NULL;

-- Update existing comments to be approved by default
UPDATE PostComment SET status = 'approved';

-- Add indexes for better performance
CREATE INDEX idx_comment_status ON PostComment(status);
CREATE INDEX idx_comment_post ON PostComment(post_id);
CREATE INDEX idx_comment_user ON PostComment(user_id);
