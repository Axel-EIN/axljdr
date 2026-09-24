<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Rang de Statut du personnage, quatrième valeur sociale de la fiche à côté de l'honneur, de la gloire et de l'infamie. Vaut 1.0 par défaut, y compris pour les fiches déjà créées.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql("ALTER TABLE fiche_personnage ADD statut NUMERIC(3, 1) DEFAULT '1.0'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fiche_personnage DROP statut');
  }
}
