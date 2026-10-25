<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025190000 extends AbstractMigration
{
  private const COLUMNS = ['part1', 'part2', 'part3', 'part4', 'part5'];

  private const PATHS = [
    '/regles/rule/' => '/rules/rule/',
    '/regles/library/' => '/rules/library/',
    '/regles/objet/' => '/rules/object/',
    '/regles/classe/' => '/rules/class/',
    '/regles/ecole/' => '/rules/dojo/',
  ];

  public function getDescription(): string
  {
    return 'rule.part1 à part5 : les liens écrits dans les textes passent de regles/ à rules/, suite au renommage de la rubrique.';
  }

  public function up(Schema $schema): void
  {
    $this->replacePaths(self::PATHS);
  }

  public function down(Schema $schema): void
  {
    $this->replacePaths(array_flip(self::PATHS));
  }

  private function replacePaths(array $paths): void
  {
    foreach (self::COLUMNS as $column) {
      $value = $column;
      foreach ($paths as $from => $to) {
        $value = "REPLACE($value, '$from', '$to')";
      }
      $this->addSql("UPDATE rule SET $column = $value");
    }
  }
}
