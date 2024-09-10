<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521102000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le modificateur de ND pour être touché (nd_modifier) sur fiche_personnage.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fiche_personnage ADD nd_modifier INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fiche_personnage DROP nd_modifier');
    }
}
