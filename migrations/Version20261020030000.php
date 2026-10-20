<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020030000 extends AbstractMigration
{
    public function getDescription(): string { return 'Adds read and seen timestamps for navigation notifications.'; }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message ADD read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE profile_like ADD seen_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE profile_visit ADD seen_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message DROP read_at');
        $this->addSql('ALTER TABLE profile_like DROP seen_at');
        $this->addSql('ALTER TABLE profile_visit DROP seen_at');
    }
}
