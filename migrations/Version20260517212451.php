<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260517212451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute compEcole<i> (smallint) et speEcole<i> (bitmask string) pour i=1..20 sur fiche_personnage.';
    }

    public function up(Schema $schema): void
    {
        $cols = [];
        for ($i = 1; $i <= 20; $i++) {
            $cols[] = "ADD comp_ecole{$i} SMALLINT DEFAULT NULL";
            $cols[] = "ADD spe_ecole{$i} VARCHAR(6) DEFAULT NULL";
        }
        $this->addSql('ALTER TABLE fiche_personnage ' . implode(', ', $cols));
    }

    public function down(Schema $schema): void
    {
        $cols = [];
        for ($i = 1; $i <= 20; $i++) {
            $cols[] = "DROP comp_ecole{$i}";
            $cols[] = "DROP spe_ecole{$i}";
        }
        $this->addSql('ALTER TABLE fiche_personnage ' . implode(', ', $cols));
    }
}
