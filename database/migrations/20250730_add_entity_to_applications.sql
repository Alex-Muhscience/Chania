ALTER TABLE applications ADD COLUMN entity_type VARCHAR(50) AFTER status, ADD COLUMN entity_id INT UNSIGNED AFTER entity_type;
