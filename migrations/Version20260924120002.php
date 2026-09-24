<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924120002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Bourse du personnage en koku, bu et zeni, reprise dans le bloc Argent du verso de la fiche imprimable.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fiche_personnage ADD koku INT DEFAULT 0, ADD bu INT DEFAULT 0, ADD zeni INT DEFAULT 0');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fiche_personnage DROP koku, DROP bu, DROP zeni');
  }
}
