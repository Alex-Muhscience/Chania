-- Migration: Add is_approved column to testimonials table
-- Date: 2025-07-21
-- Purpose: Add approval status column for testimonial moderation

-- Add is_approved column to testimonials table
ALTER TABLE testimonials 
ADD COLUMN is_approved TINYINT(1) DEFAULT 0 AFTER is_featured;

-- Add index for the new column to improve query performance
ALTER TABLE testimonials 
ADD INDEX idx_testimonials_approved (is_approved);

-- Update existing testimonials to be approved by default (optional)
-- Uncomment the line below if you want existing testimonials to be automatically approved
-- UPDATE testimonials SET is_approved = 1 WHERE deleted_at IS NULL;

-- Note: The dashboard query expects this column to exist for counting approved testimonials
-- Query: SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL AND is_approved = 1

-- Migration completed successfully
SELECT 'Migration completed: is_approved column added to testimonials table' AS status;
