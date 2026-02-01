<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260201061429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD reset_token_expires_at DATETIME DEFAULT NULL, DROP sexe, DROP situation, DROP bio, DROP recherche, CHANGE avatar reset_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD sexe VARCHAR(10) DEFAULT NULL, ADD situation VARCHAR(20) DEFAULT NULL, ADD bio LONGTEXT DEFAULT NULL, ADD recherche VARCHAR(10) DEFAULT NULL, DROP reset_token_expires_at, CHANGE reset_token avatar VARCHAR(255) DEFAULT NULL');
    }
}
