<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108140001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'rule : ajout de part_links, liens de chaque partie vers des règles, lores et bibliothèques.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE rule ADD part_links JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE rule DROP part_links');
  }
}
