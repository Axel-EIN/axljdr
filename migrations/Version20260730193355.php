<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260730193355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rend saison.image nullable : NOT NULL en base alors que le mapping autorise le vide.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE saison CHANGE image image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE saison CHANGE image image VARCHAR(255) NOT NULL');
    }
}
