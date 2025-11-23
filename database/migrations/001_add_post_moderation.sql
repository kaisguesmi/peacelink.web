-- Add status column to Post table for moderation
ALTER TABLE Post 
ADD COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending';

-- Update existing posts to be approved by default
UPDATE Post SET status = 'approved';

-- Add index for faster status-based queries
CREATE INDEX idx_post_status ON Post(status);

-- Add moderation_notes column for admin comments
ALTER TABLE Post
ADD COLUMN moderation_notes TEXT NULL AFTER status;
