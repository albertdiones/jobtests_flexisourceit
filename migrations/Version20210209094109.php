<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210209094109 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create the tenant table template';
    }

    public function up(Schema $schema) : void
    {


        # It is much easier and reliable to create mysql tables using "create table like" query instead of having a DDL hardcoded on php
        # So let's create a 'template' db from which we can copy (from) new tenant tables
        $this->connection->beginTransaction();
        $this->addSql("CREATE DATABASE IF NOT EXISTS tenant_template");
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS `tenant_template`.`categories` (
                `id` int NOT NULL AUTO_INCREMENT,
                `name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT
                NULL,
                `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `enabled` bit(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_name` (`name`),
                KEY `idx_enabled` (`enabled`)
            );
SQL
        );
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS `tenant_template`.`products` (
                `id` int NOT NULL AUTO_INCREMENT,
                `name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT
                NULL,
                `price` decimal(10,0) NOT NULL,
                `category_id` int NOT NULL,
                `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `enabled` bit(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_name` (`name`),
                KEY `idx_enabled` (`enabled`),
                KEY `category_id` (`category_id`),
                CONSTRAINT `fk_category_id` FOREIGN KEY (`category_id`) REFERENCES
                `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                );
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
