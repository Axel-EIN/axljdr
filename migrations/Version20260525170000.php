<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260525170000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Ajoute la colonne locked à lieu (verrou MJ, identique à archive et personnage).';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu ADD locked TINYINT(1) NOT NULL DEFAULT 0');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu DROP locked');
  }
}
