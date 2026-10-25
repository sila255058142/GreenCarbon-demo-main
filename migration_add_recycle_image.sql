USE greenloop;

ALTER TABLE recycles
ADD COLUMN image VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'ชื่อไฟล์รูปสินค้าใน uploads/';
