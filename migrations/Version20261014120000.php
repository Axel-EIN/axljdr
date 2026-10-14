<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014120000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'lieu.description : les liens de lieux écrits par le Baliseur passent de empire/lieu/ à world/lieu/, suite au renommage de la rubrique.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE lieu SET description = REPLACE(description, '../../empire/lieu/', '../../world/lieu/') WHERE description LIKE '%../../empire/lieu/%'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE lieu SET description = REPLACE(description, '../../world/lieu/', '../../empire/lieu/') WHERE description LIKE '%../../world/lieu/%'");
  }
}
