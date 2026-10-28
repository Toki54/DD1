<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cleans up live account data on deletion while preserving deleted-conversation archives.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE profile_visit DROP FOREIGN KEY FK_B4AA76EB38C9FE8F');
        $this->addSql('ALTER TABLE profile_visit DROP FOREIGN KEY FK_B4AA76EB70BEE6D');
        $this->addSql('ALTER TABLE profile_visit ADD CONSTRAINT FK_B4AA76EB38C9FE8F FOREIGN KEY (visited_id) REFERENCES user_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_visit ADD CONSTRAINT FK_B4AA76EB70BEE6D FOREIGN KEY (visitor_id) REFERENCES user_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_like DROP FOREIGN KEY FK_1AABCF27979F103A');
        $this->addSql('ALTER TABLE profile_like DROP FOREIGN KEY FK_1AABCF27E2ED1879');
        $this->addSql('ALTER TABLE profile_like ADD CONSTRAINT FK_1AABCF27979F103A FOREIGN KEY (liker_id) REFERENCES user_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_like ADD CONSTRAINT FK_1AABCF27E2ED1879 FOREIGN KEY (liked_id) REFERENCES user_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FCD53EDB6');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3A76ED395');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE deleted_message DROP FOREIGN KEY FK_EC3A8A18F624B39D');
        $this->addSql('ALTER TABLE deleted_message DROP FOREIGN KEY FK_EC3A8A18CD53EDB6');
        $this->addSql('ALTER TABLE deleted_message MODIFY sender_id INT DEFAULT NULL, MODIFY receiver_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deleted_message ADD CONSTRAINT FK_EC3A8A18F624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE deleted_message ADD CONSTRAINT FK_EC3A8A18CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE deleted_conversation DROP FOREIGN KEY FK_777D9B91A76ED395');
        $this->addSql('ALTER TABLE deleted_conversation DROP FOREIGN KEY FK_777D9B91AA1F50EF');
        $this->addSql('ALTER TABLE deleted_conversation MODIFY user_id INT DEFAULT NULL, MODIFY deleted_with_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deleted_conversation ADD CONSTRAINT FK_777D9B91A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE deleted_conversation ADD CONSTRAINT FK_777D9B91AA1F50EF FOREIGN KEY (deleted_with_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Archived rows may reference deleted accounts and cannot be restored safely.');
    }
}
