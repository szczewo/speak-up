CREATE DATABASE IF NOT EXISTS development;
CREATE USER IF NOT EXISTS 'development'@'%' IDENTIFIED BY 'development';
GRANT ALL PRIVILEGES ON development.* TO 'development'@'%';
FLUSH PRIVILEGES;
