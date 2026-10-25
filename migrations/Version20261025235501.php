<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025235501 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'character_unlock.entity : la clé d\'entité objet devient object, suite au renommage de la clé.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE character_unlock SET entity = 'object' WHERE entity = 'objet'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE character_unlock SET entity = 'objet' WHERE entity = 'object'");
  }
}
