<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014120003 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'development.content : les liens de lieux écrits par le Baliseur passent de empire/lieu/ à world/lieu/, suite au renommage de la rubrique.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE development SET content = REPLACE(content, '../../empire/lieu/', '../../world/lieu/') WHERE content LIKE '%../../empire/lieu/%'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE development SET content = REPLACE(content, '../../world/lieu/', '../../empire/lieu/') WHERE content LIKE '%../../world/lieu/%'");
  }
}
