<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025160003 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'development.content : les liens de lieux écrits par le Baliseur passent de world/lieu/ à world/location/, suite au renommage de la route.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE development SET content = REPLACE(content, '../../world/lieu/', '../../world/location/') WHERE content LIKE '%../../world/lieu/%'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE development SET content = REPLACE(content, '../../world/location/', '../../world/lieu/') WHERE content LIKE '%../../world/location/%'");
  }
}
