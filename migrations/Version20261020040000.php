<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020040000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Stores the launch baseline for the men premium promotion.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE promotion_campaign (name VARCHAR(50) NOT NULL, start_user_id INT NOT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE promotion_campaign');
    }
}
