<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826120002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Ajoute la colonne locked à ecole (verrou MJ, identique à archive, lieu et personnage).';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE ecole ADD locked TINYINT(1) NOT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE ecole DROP locked');
  }
}
