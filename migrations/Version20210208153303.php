<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210208153303 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'base directory tables';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->connection->beginTransaction();
        $this->addSql("CREATE DATABASE IF NOT EXISTS  `directory`");


        $this->addSql(
<<<SQL
            CREATE TABLE IF NOT EXISTS `directory`.`users` (
                `id` int NOT NULL AUTO_INCREMENT,
                `email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT
                NULL,
                `password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
                NOT NULL,
                `api_key` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
                DEFAULT NULL,
                `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE
                CURRENT_TIMESTAMP,
                `enabled` bit(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_username` (`email`),
                UNIQUE KEY `idx_username_pass` (`email`,`password`),
                UNIQUE KEY `idx_apikey` (`api_key`),
                KEY `idx_enabled` (`enabled`)
                );
SQL
);

        $this->addSql(
<<<SQL
                CREATE TABLE `tenants` (
                `id` int NOT NULL AUTO_INCREMENT,
                `tenant_name` varchar(256) CHARACTER SET utf8mb4 COLLATE
                utf8mb4_unicode_ci NOT NULL,
                `tenant_db` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
                NOT NULL,
                `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `enabled` bit(1) NOT NULL DEFAULT b'1',
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_name` (`tenant_name`),
                KEY `idx_enabled` (`enabled`)
                );
SQL
);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
