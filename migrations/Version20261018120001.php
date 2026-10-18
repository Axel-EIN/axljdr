<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261018120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Le MJ ne peut plus naviguer sans personnage : le drapeau without_character de l\'utilisateur est supprimé.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE utilisateur DROP without_character');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE utilisateur ADD without_character TINYINT(1) DEFAULT 0 NOT NULL');
  }
}
