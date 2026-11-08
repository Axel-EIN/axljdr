<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108140002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'lore : ajout de part_links, liens de chaque partie vers des règles, lores et bibliothèques.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lore ADD part_links JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lore DROP part_links');
  }
}
