<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renomme reduction_special en reduction_modifier et bascule initiative_modifier (VARCHAR XgY) en INT.';
    }

    public function up(Schema $schema): void
    {
        // Renomme reduction_special → reduction_modifier (données conservées)
        $this->addSql('ALTER TABLE fiche_personnage CHANGE reduction_special reduction_modifier INT DEFAULT NULL');

        // initiative_modifier passe de VARCHAR(10) "XgY" à INT (les valeurs XgY existantes ne sont pas convertibles)
        $this->addSql('ALTER TABLE fiche_personnage DROP initiative_modifier');
        $this->addSql('ALTER TABLE fiche_personnage ADD initiative_modifier INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fiche_personnage CHANGE reduction_modifier reduction_special INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_personnage DROP initiative_modifier');
        $this->addSql('ALTER TABLE fiche_personnage ADD initiative_modifier VARCHAR(10) DEFAULT NULL');
    }
}
