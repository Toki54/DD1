<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604193630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_interest DROP FOREIGN KEY FK_8CB3FE676C066AFE');
        $this->addSql('ALTER TABLE user_interest DROP FOREIGN KEY FK_8CB3FE67EEB16BFD');
        $this->addSql('DROP TABLE user_interest');
        $this->addSql('ALTER TABLE profile_visit DROP FOREIGN KEY FK_B4AA76EB38C9FE8F');
        $this->addSql('ALTER TABLE profile_visit DROP FOREIGN KEY FK_B4AA76EB70BEE6D');
        $this->addSql('ALTER TABLE profile_visit CHANGE visitor_id visitor_id INT NOT NULL, CHANGE visited_id visited_id INT NOT NULL');
        $this->addSql('ALTER TABLE profile_visit ADD CONSTRAINT FK_B4AA76EB38C9FE8F FOREIGN KEY (visited_id) REFERENCES user_profile (id)');
        $this->addSql('ALTER TABLE profile_visit ADD CONSTRAINT FK_B4AA76EB70BEE6D FOREIGN KEY (visitor_id) REFERENCES user_profile (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_interest (id INT AUTO_INCREMENT NOT NULL, source_user_id INT DEFAULT NULL, target_user_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_8CB3FE67EEB16BFD (source_user_id), INDEX IDX_8CB3FE676C066AFE (target_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_interest ADD CONSTRAINT FK_8CB3FE676C066AFE FOREIGN KEY (target_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_interest ADD CONSTRAINT FK_8CB3FE67EEB16BFD FOREIGN KEY (source_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE profile_visit DROP FOREIGN KEY FK_B4AA76EB38C9FE8F');
        $this->addSql('ALTER TABLE profile_visit DROP FOREIGN KEY FK_B4AA76EB70BEE6D');
        $this->addSql('ALTER TABLE profile_visit CHANGE visited_id visited_id INT DEFAULT NULL, CHANGE visitor_id visitor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profile_visit ADD CONSTRAINT FK_B4AA76EB38C9FE8F FOREIGN KEY (visited_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE profile_visit ADD CONSTRAINT FK_B4AA76EB70BEE6D FOREIGN KEY (visitor_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
