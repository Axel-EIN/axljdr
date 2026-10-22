<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022180001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Le clan perd son image de territoires : la carte interactive affiche désormais ses territoires.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE clan DROP territoire_carte');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE clan ADD territoire_carte VARCHAR(255) DEFAULT NULL');
  }
}
