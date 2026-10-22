<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022120002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Le clan reçoit une position pour l'emblème de la carte de ses territoires.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE clan ADD emblem_x NUMERIC(5, 2) DEFAULT NULL, ADD emblem_y NUMERIC(5, 2) DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE clan DROP emblem_x, DROP emblem_y');
  }
}
