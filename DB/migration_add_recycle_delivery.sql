USE greenloop;

ALTER TABLE recycles
ADD COLUMN delivery_type ENUM('self','pickup') NOT NULL DEFAULT 'self',
ADD COLUMN address VARCHAR(500) NOT NULL DEFAULT '';
