<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Résumé court d'un avantage ou d'un désavantage, pour les quelques lignes que lui laisse le verso de la fiche imprimable.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE avantage ADD summary VARCHAR(255) DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE avantage DROP summary');
  }
}
