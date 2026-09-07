<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260906130001 extends AbstractMigration
{
  private const TABLES = [
    'personnage', 'lieu', 'clan', 'archive', 'lore', 'objet',
    'ecole', 'rule', 'library', 'sort', 'competence', 'avantage', 'episode',
  ];

  public function getDescription(): string
  {
    return 'Le palier teasé commun disparaît : access passe à cinq valeurs (SECRET, LOCKED, COMMON_AUTO, PUBLISHED_PLAYER, PUBLISHED_GUEST). Les éléments teasés communs rejoignent le déblocage automatique, les deux paliers publiés descendent d\'un cran.';
  }

  public function up(Schema $schema): void
  {
    foreach (self::TABLES as $table) {
      $this->addSql(sprintf('UPDATE %s SET access = 2 WHERE access = 3', $table));
      $this->addSql(sprintf('UPDATE %s SET access = 3 WHERE access = 4', $table));
      $this->addSql(sprintf('UPDATE %s SET access = 4 WHERE access = 5', $table));
    }
  }

  public function down(Schema $schema): void
  {
    foreach (self::TABLES as $table) {
      $this->addSql(sprintf('UPDATE %s SET access = 5 WHERE access = 4', $table));
      $this->addSql(sprintf('UPDATE %s SET access = 4 WHERE access = 3', $table));
    }
  }
}
