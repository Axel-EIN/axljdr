<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261113150000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'utilisateur : ajoute le nom affiché, montré aux autres joueurs à la place du pseudo de connexion.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE utilisateur ADD display_name VARCHAR(50) DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE utilisateur DROP display_name');
  }
}
