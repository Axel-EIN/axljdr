<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260525120000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Supprime la colonne region de lieu (image carte régionale), remplacée par locX/locY sur la carte générale.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu DROP region');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu ADD region VARCHAR(255) DEFAULT NULL');
  }
}
