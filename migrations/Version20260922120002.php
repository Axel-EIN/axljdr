<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922120002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Résumé court des cinq techniques de rang et de la technique spéciale d'une école, pour les cases de la fiche imprimable.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE ecole ADD tech1_summary VARCHAR(255) DEFAULT NULL, ADD tech2_summary VARCHAR(255) DEFAULT NULL, ADD tech3_summary VARCHAR(255) DEFAULT NULL, ADD tech4_summary VARCHAR(255) DEFAULT NULL, ADD tech5_summary VARCHAR(255) DEFAULT NULL, ADD tech_special_summary VARCHAR(255) DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE ecole DROP tech1_summary, DROP tech2_summary, DROP tech3_summary, DROP tech4_summary, DROP tech5_summary, DROP tech_special_summary');
  }
}
