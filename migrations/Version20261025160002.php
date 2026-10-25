<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025160002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'personnage.description : les liens de lieux écrits par le Baliseur passent de world/lieu/ à world/location/, suite au renommage de la route.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE personnage SET description = REPLACE(description, '../../world/lieu/', '../../world/location/') WHERE description LIKE '%../../world/lieu/%'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE personnage SET description = REPLACE(description, '../../world/location/', '../../world/lieu/') WHERE description LIKE '%../../world/location/%'");
  }
}
