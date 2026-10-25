<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025150000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'L\'utilisateur reçoit le mode de navigation Joueur : un MJ en mode Joueur perd ses droits de MJ et navigue avec son personnage principal.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE utilisateur ADD player_mode TINYINT(1) DEFAULT 0 NOT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE utilisateur DROP player_mode');
  }
}
