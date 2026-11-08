<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108160000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'sort : les sorts publics sont datés du 1er septembre 2026.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('UPDATE sort SET published_at = \'2026-09-01 00:00:00\' WHERE access = 3');
  }

  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException(
      'Les dates de publication précédentes sont écrasées : restaurer une sauvegarde.'
    );
  }
}
