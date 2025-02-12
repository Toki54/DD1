<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212195916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_profile (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sex VARCHAR(50) NOT NULL, situation VARCHAR(100) DEFAULT NULL, research VARCHAR(100) DEFAULT NULL, biography LONGTEXT DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, photos JSON DEFAULT NULL, UNIQUE INDEX UNIQ_D95AB405A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_photo DROP FOREIGN KEY FK_F6757F40A76ED395');
        $this->addSql('DROP TABLE user_photo');
        $this->addSql('ALTER TABLE user DROP sexe, DROP situation, DROP bio, DROP recherche, DROP avatar, DROP pseudo');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_photo (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, photos JSON DEFAULT NULL, INDEX IDX_F6757F40A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_photo ADD CONSTRAINT FK_F6757F40A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('ALTER TABLE user ADD sexe VARCHAR(10) DEFAULT NULL, ADD situation VARCHAR(20) DEFAULT NULL, ADD bio LONGTEXT DEFAULT NULL, ADD recherche VARCHAR(10) DEFAULT NULL, ADD avatar VARCHAR(255) DEFAULT NULL, ADD pseudo VARCHAR(80) NOT NULL');
    }
}
