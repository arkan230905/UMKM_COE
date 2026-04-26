-- Make kategori column nullable in beban_operasional table
ALTER TABLE `beban_operasional` 
MODIFY COLUMN `kategori` VARCHAR(255) NULL;

-- Verify the change
DESCRIBE `beban_operasional`;
