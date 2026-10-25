<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025210000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'lieu.region_desc : les liens de lieux écrits par le Baliseur passent de world/lieu/ à world/location/, champ oublié par Version20261025160000.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE lieu SET region_desc = REPLACE(region_desc, '../../world/lieu/', '../../world/location/') WHERE region_desc LIKE '%../../world/lieu/%'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE lieu SET region_desc = REPLACE(region_desc, '../../world/location/', '../../world/lieu/') WHERE region_desc LIKE '%../../world/location/%'");
  }
}
