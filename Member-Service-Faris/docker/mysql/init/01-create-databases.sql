CREATE DATABASE IF NOT EXISTS tubes_iae_keanggotaan
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON tubes_iae_keanggotaan.* TO 'iae_user'@'%';

FLUSH PRIVILEGES;
