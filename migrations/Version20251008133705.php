<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251008133705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE deleted_conversation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, deleted_with_id INT NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_777D9B91A76ED395 (user_id), INDEX IDX_777D9B91AA1F50EF (deleted_with_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deleted_message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, content LONGTEXT NOT NULL, sent_at DATETIME NOT NULL, deleted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EC3A8A18F624B39D (sender_id), INDEX IDX_EC3A8A18CD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, content LONGTEXT NOT NULL, sent_at DATETIME NOT NULL, is_chat_request TINYINT(1) NOT NULL, INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307FCD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile_like (id INT AUTO_INCREMENT NOT NULL, liker_id INT NOT NULL, liked_id INT NOT NULL, liked_at DATETIME NOT NULL, INDEX IDX_1AABCF27979F103A (liker_id), INDEX IDX_1AABCF27E2ED1879 (liked_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile_visit (id INT AUTO_INCREMENT NOT NULL, visited_id INT NOT NULL, visitor_id INT NOT NULL, visited_at DATETIME NOT NULL, INDEX IDX_B4AA76EB38C9FE8F (visited_id), INDEX IDX_B4AA76EB70BEE6D (visitor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, plan VARCHAR(50) NOT NULL, price DOUBLE PRECISION NOT NULL, active TINYINT(1) NOT NULL, stripe_subscription_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, stripe_session_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_A3C664D3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, pseudo VARCHAR(80) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_profile (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sex VARCHAR(50) DEFAULT NULL, situation VARCHAR(100) DEFAULT NULL, research JSON DEFAULT NULL, biography LONGTEXT DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, photos JSON DEFAULT NULL, department VARCHAR(100) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, birthdate DATE DEFAULT NULL, UNIQUE INDEX UNIQ_D95AB405A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE deleted_conversation ADD CONSTRAINT FK_777D9B91A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE deleted_conversation ADD CONSTRAINT FK_777D9B91AA1F50EF FOREIGN KEY (deleted_with_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE deleted_message ADD CONSTRAINT FK_EC3A8A18F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE deleted_message ADD CONSTRAINT FK_EC3A8A18CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE profile_like ADD CONSTRAINT FK_1AABCF27979F103A FOREIGN KEY (liker_id) REFERENCES user_profile (id)');
        $this->addSql('ALTER TABLE profile_like ADD CONSTRAINT FK_1AABCF27E2ED1879 FOREIGN KEY (liked_id) REFERENCES user_profile (id)');
        $this->addSql('ALTER TABLE profile_visit ADD CONSTRAINT FK_B4AA76EB38C9FE8F FOREIGN KEY (visited_id) REFERENCES user_profile (id)');
        $this->addSql('ALTER TABLE profile_visit ADD CONSTRAINT FK_B4AA76EB70BEE6D FOREIGN KEY (visitor_id) REFERENCES user_profile (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deleted_conversation DROP FOREIGN KEY FK_777D9B91A76ED395');
        $this->addSql('ALTER TABLE deleted_conversation DROP FOREIGN KEY FK_777D9B91AA1F50EF');
        $this->addSql('ALTER TABLE deleted_message DROP FOREIGN KEY FK_EC3A8A18F624B39D');
        $this->addSql('ALTER TABLE deleted_message DROP FOREIGN KEY FK_EC3A8A18CD53EDB6');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FCD53EDB6');
        $this->addSql('ALTER TABLE profile_like DROP FOREIGN KEY FK_1AABCF27979F103A');
        $this->addSql('ALTER TABLE profile_like DROP FOREIGN KEY FK_1AABCF27E2ED1879');
        $this->addSql('ALTER TABLE profile_visit DROP FOREIGN KEY FK_B4AA76EB38C9FE8F');
        $this->addSql('ALTER TABLE profile_visit DROP FOREIGN KEY FK_B4AA76EB70BEE6D');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3A76ED395');
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('DROP TABLE deleted_conversation');
        $this->addSql('DROP TABLE deleted_message');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE profile_like');
        $this->addSql('DROP TABLE profile_visit');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
