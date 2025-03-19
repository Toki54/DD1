<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250319222325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE deleted_conversation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, deleted_with_id INT NOT NULL, INDEX IDX_777D9B91A76ED395 (user_id), INDEX IDX_777D9B91AA1F50EF (deleted_with_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE deleted_conversation ADD CONSTRAINT FK_777D9B91A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE deleted_conversation ADD CONSTRAINT FK_777D9B91AA1F50EF FOREIGN KEY (deleted_with_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deleted_conversation DROP FOREIGN KEY FK_777D9B91A76ED395');
        $this->addSql('ALTER TABLE deleted_conversation DROP FOREIGN KEY FK_777D9B91AA1F50EF');
        $this->addSql('DROP TABLE deleted_conversation');
    }
}
