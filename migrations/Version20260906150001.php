<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260906150001 extends AbstractMigration
{
  private const TABLES = [
    'personnage', 'lieu', 'clan', 'archive', 'lore', 'objet',
    'ecole', 'rule', 'library', 'sort', 'competence', 'avantage', 'episode',
  ];

  public function getDescription(): string
  {
    return 'Les deux paliers publiés fusionnent en un seul PUBLIC : c\'est désormais la date de publication qui distingue le contenu initial (sans date) de ce qui paraît ensuite (daté, donc listé à l\'accueil et éligible au badge Nouveau).';
  }

  public function up(Schema $schema): void
  {
    foreach (self::TABLES as $table) {
      $this->addSql(sprintf('UPDATE %s SET access = 3 WHERE access = 4', $table));
    }
  }

  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException(
      'Les deux paliers publiés sont confondus : seule la date de publication permettrait de les redistinguer.'
    );
  }
}
