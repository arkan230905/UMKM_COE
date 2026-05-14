-- Create new database user for Laravel
CREATE USER IF NOT EXISTS 'laravel'@'localhost' IDENTIFIED BY 'laravel123';
CREATE USER IF NOT EXISTS 'laravel'@'127.0.0.1' IDENTIFIED BY 'laravel123';
CREATE USER IF NOT EXISTS 'laravel'@'%' IDENTIFIED BY 'laravel123';

GRANT ALL PRIVILEGES ON eadt_umkm.* TO 'laravel'@'localhost';
GRANT ALL PRIVILEGES ON eadt_umkm.* TO 'laravel'@'127.0.0.1';
GRANT ALL PRIVILEGES ON eadt_umkm.* TO 'laravel'@'%';

FLUSH PRIVILEGES;
