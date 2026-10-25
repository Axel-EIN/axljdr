<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025233000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'library.entity : la clé d\'entité competence devient skill, suite au renommage de la clé.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE library SET entity = 'skill' WHERE entity = 'competence'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE library SET entity = 'competence' WHERE entity = 'skill'");
  }
}
