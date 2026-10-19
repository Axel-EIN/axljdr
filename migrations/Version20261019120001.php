<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Le clan reçoit une position et un zoom pour centrer la carte de ses territoires.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE clan ADD loc_x NUMERIC(5, 2) DEFAULT NULL, ADD loc_y NUMERIC(5, 2) DEFAULT NULL, ADD zoom NUMERIC(3, 2) DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE clan DROP loc_x, DROP loc_y, DROP zoom');
  }
}
