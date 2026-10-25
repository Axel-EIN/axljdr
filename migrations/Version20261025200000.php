<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025200000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'lieu.description et lieu.region_desc : les liens de personnages écrits par le Baliseur passent de personnages/profil/ à characters/profile/, suite au renommage de la rubrique.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE lieu SET description = REPLACE(description, '../../personnages/profil/', '../../characters/profile/') WHERE description LIKE '%../../personnages/profil/%'");
    $this->addSql("UPDATE lieu SET region_desc = REPLACE(region_desc, '../../personnages/profil/', '../../characters/profile/') WHERE region_desc LIKE '%../../personnages/profil/%'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE lieu SET description = REPLACE(description, '../../characters/profile/', '../../personnages/profil/') WHERE description LIKE '%../../characters/profile/%'");
    $this->addSql("UPDATE lieu SET region_desc = REPLACE(region_desc, '../../characters/profile/', '../../personnages/profil/') WHERE region_desc LIKE '%../../characters/profile/%'");
  }
}
