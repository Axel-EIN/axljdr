<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022120003 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Le lieu perd son image de région : la carte interactive affiche désormais la région.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu DROP region_map');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu ADD region_map VARCHAR(255) DEFAULT NULL');
  }
}
