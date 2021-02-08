<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210208165115 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create the first user';
    }

    public function up(Schema $schema) : void
    {
        # use sha1 for password
        $this->addSql("INSERT INTO users(email,password,api_key) VALUES('albertdiones@gmail.com','bdfcd25b121871ebd5c7c9195f4d34afd9470846','bdfcd25b121871ebd5c7c9195f4d34afd9470846')");
        # just added api key for future testing so I don't have to make a migration to update it
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
