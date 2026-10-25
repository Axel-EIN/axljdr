<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025230000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'library.entity : la clé d\'entité avantage devient advantage, suite au renommage de la clé.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE library SET entity = 'advantage' WHERE entity = 'avantage'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE library SET entity = 'avantage' WHERE entity = 'advantage'");
  }
}
