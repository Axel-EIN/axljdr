<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022160400 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "La fiche de personnage reçoit une position libre sur la carte, prioritaire sur le lieu de la dernière scène.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fiche_personnage ADD loc_x NUMERIC(5, 2) DEFAULT NULL, ADD loc_y NUMERIC(5, 2) DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fiche_personnage DROP loc_x, DROP loc_y');
  }
}
