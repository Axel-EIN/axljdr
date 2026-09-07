<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260906170001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Le joueur peut naviguer sans personnage : un drapeau sur l\'utilisateur court-circuite le personnage principal et sa reprise automatique.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE utilisateur ADD without_character TINYINT(1) DEFAULT 0 NOT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE utilisateur DROP without_character');
  }
}
