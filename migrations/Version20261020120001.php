<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Le lieu reçoit un zoom, une image et un texte pour sa région ; la carte et les quartiers deviennent le plan.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu CHANGE carte plan_map VARCHAR(255) DEFAULT NULL, CHANGE quartiers plan_desc LONGTEXT DEFAULT NULL, ADD zoom NUMERIC(3, 2) DEFAULT NULL, ADD region_map VARCHAR(255) DEFAULT NULL, ADD region_desc LONGTEXT DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu CHANGE plan_map carte VARCHAR(255) DEFAULT NULL, CHANGE plan_desc quartiers LONGTEXT DEFAULT NULL, DROP zoom, DROP region_map, DROP region_desc');
  }
}
