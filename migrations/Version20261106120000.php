<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Filesystem\Filesystem;

class Version20261106120000 extends AbstractMigration
{
  protected const TABLE = 'rule';
  protected const FOLDER = 'rules';

  private $moves = [];

  public function getDescription(): string
  {
    return static::TABLE . '.pdf : PDF déplacés de public/assets vers storage/pdf/' . static::FOLDER . ', hors de la racine web.';
  }

  public function up(Schema $schema): void
  {
    $root = dirname(__DIR__);
    $rows = $this->connection->fetchAllAssociative('SELECT id, pdf FROM ' . static::TABLE . " WHERE pdf LIKE 'assets/%'");

    foreach ($rows as $row) {
      $target = static::FOLDER . '/' . basename($row['pdf']);
      $this->moves[] = [$root . '/public/' . $row['pdf'], $root . '/storage/pdf/' . $target];
      $this->addSql('UPDATE ' . static::TABLE . ' SET pdf = ? WHERE id = ?', [$target, $row['id']]);
    }
  }

  public function postUp(Schema $schema): void
  {
    $filesystem = new Filesystem();

    foreach ($this->moves as [$from, $to]) {
      if (!is_file($from)) {
        $this->write('Fichier absent, chemin mis à jour quand même : ' . $from);
        continue;
      }

      $filesystem->mkdir(dirname($to));
      $filesystem->rename($from, $to, true);
      $this->write('Déplacé : ' . $from . ' -> ' . $to);
    }
  }

  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException('Les PDF restent dans storage/pdf.');
  }
}
