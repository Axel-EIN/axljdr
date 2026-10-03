<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261003120002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'lieu reçoit manual_unlock : le mode de déblocage se sépare du palier access. Les éléments secrets et bloqués restent en déblocage individuel, les communs restent en déblocage automatique.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu ADD manual_unlock TINYINT(1) DEFAULT 0 NOT NULL');
    $this->addSql('UPDATE lieu SET manual_unlock = 1 WHERE access IN (0, 1)');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu DROP manual_unlock');
  }
}
