<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260927180000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Suppression de la valeur des compétences de départ offertes par l'école, jamais utilisée.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE ecole DROP valeur_competences_depart');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE ecole ADD valeur_competences_depart SMALLINT DEFAULT NULL');
  }
}
