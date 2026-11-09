<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109130010 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'personnage : les personnages sans date de publication sont datés du 1er mars 2013, quel que soit leur accès.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('UPDATE personnage SET published_at = \'2013-03-01 00:00:00\' WHERE published_at IS NULL');
  }

  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException(
      'Les éléments datés par cette migration ne se distinguent plus des autres : restaurer une sauvegarde.'
    );
  }
}
