<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915140003 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'La description de la bibliothèque devient facultative : le formulaire la donnait déjà comme telle, la colonne la refusait encore et l\'enregistrement échouait.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE library CHANGE description description LONGTEXT DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE library CHANGE description description LONGTEXT NOT NULL');
  }
}
