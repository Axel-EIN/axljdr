<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260827120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Rend clan.genre nullable : le formulaire propose "Pas de genre défini", la colonne refusait NULL.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE clan CHANGE genre genre VARCHAR(1) DEFAULT NULL');
    $this->addSql("UPDATE clan SET genre = NULL WHERE genre = ''");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE clan SET genre = '' WHERE genre IS NULL");
    $this->addSql('ALTER TABLE clan CHANGE genre genre VARCHAR(1) NOT NULL');
  }
}
