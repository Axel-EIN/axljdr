<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109130012 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'sort : les sorts sans date de publication sont datés du 1er septembre 2025, quel que soit leur accès.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('UPDATE sort SET published_at = \'2025-09-01 00:00:00\' WHERE published_at IS NULL');
  }

  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException(
      'Les éléments datés par cette migration ne se distinguent plus des autres : restaurer une sauvegarde.'
    );
  }
}
