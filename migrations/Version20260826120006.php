<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826120006 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Retire le DEFAULT 0 de lieu.locked : le mapping n\'en déclare aucun, la base restait désynchronisée.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu CHANGE locked locked TINYINT(1) NOT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu CHANGE locked locked TINYINT(1) NOT NULL DEFAULT 0');
  }
}
