<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924120006 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Notes libres du joueur sur sa fiche, reprises dans la zone Notes de Scénario et Campagne du verso imprimable.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fiche_personnage ADD notes LONGTEXT DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fiche_personnage DROP notes');
  }
}
