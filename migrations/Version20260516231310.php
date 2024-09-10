<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516231310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add specialisations1..20 bitmask columns to fiche_personnage';
    }

    public function up(Schema $schema): void
    {
        $cols = [];
        for ($i = 1; $i <= 20; $i++) {
            $cols[] = "ADD specialisations{$i} VARCHAR(6) DEFAULT NULL";
        }
        $this->addSql('ALTER TABLE fiche_personnage ' . implode(', ', $cols));
    }

    public function down(Schema $schema): void
    {
        $cols = [];
        for ($i = 1; $i <= 20; $i++) {
            $cols[] = "DROP specialisations{$i}";
        }
        $this->addSql('ALTER TABLE fiche_personnage ' . implode(', ', $cols));
    }
}
