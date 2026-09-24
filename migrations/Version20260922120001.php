<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Résumé court des deux capacités de maîtrise d'une compétence, pour la colonne étroite de la fiche imprimable.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE competence ADD mastery3_summary VARCHAR(120) DEFAULT NULL, ADD mastery6_summary VARCHAR(120) DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE competence DROP mastery3_summary, DROP mastery6_summary');
  }
}
