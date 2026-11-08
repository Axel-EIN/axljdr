<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108150000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'chapitre : ajout de prologue, qui marque le chapitre comme prologue de sa saison.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE chapitre ADD prologue TINYINT(1) DEFAULT 0 NOT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE chapitre DROP prologue');
  }
}
