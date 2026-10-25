<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025235000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'character_unlock.entity : la clé d\'entité lieu devient location, suite au renommage de la clé.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE character_unlock SET entity = 'location' WHERE entity = 'lieu'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE character_unlock SET entity = 'lieu' WHERE entity = 'location'");
  }
}
