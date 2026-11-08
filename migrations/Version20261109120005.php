<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109120005 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'archive : les archives publiques sans date de publication sont datées du 1er septembre 2026.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('UPDATE archive SET published_at = \'2026-09-01 00:00:00\' WHERE access = 3 AND published_at IS NULL');
  }

  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException(
      'Les éléments datés par cette migration ne se distinguent plus des autres : restaurer une sauvegarde.'
    );
  }
}
