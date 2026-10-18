<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261018120002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Le drapeau est_majeur du clan devient un type de faction : 0 Majeur, 1 Mineur, 2 Non aligné, 3 Ennemi.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE clan ADD type SMALLINT DEFAULT 1 NOT NULL');
    $this->addSql("UPDATE clan SET type = CASE WHEN est_majeur = 1 THEN 0 WHEN nom = 'Confrérie' THEN 2 WHEN nom = 'Adeptes du Sang' THEN 3 ELSE 1 END");
    $this->addSql('ALTER TABLE clan DROP est_majeur');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE clan ADD est_majeur SMALLINT DEFAULT NULL');
    $this->addSql('UPDATE clan SET est_majeur = CASE WHEN type = 0 THEN 1 ELSE 0 END');
    $this->addSql('ALTER TABLE clan DROP type');
  }
}
