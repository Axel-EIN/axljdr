<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025234000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'character_unlock.entity : la clé d\'entité ecole devient dojo, suite au renommage de la clé.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE character_unlock SET entity = 'dojo' WHERE entity = 'ecole'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE character_unlock SET entity = 'ecole' WHERE entity = 'dojo'");
  }
}
