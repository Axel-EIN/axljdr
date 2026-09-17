<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260917120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Le lore passe de trois à cinq parties, comme la règle : deux nouveaux blocs titre / texte / aside, tous facultatifs.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lore ADD part4 LONGTEXT DEFAULT NULL, ADD part4titre VARCHAR(255) DEFAULT NULL, ADD part4aside LONGTEXT DEFAULT NULL, ADD part5 LONGTEXT DEFAULT NULL, ADD part5titre VARCHAR(255) DEFAULT NULL, ADD part5aside LONGTEXT DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lore DROP part4, DROP part4titre, DROP part4aside, DROP part5, DROP part5titre, DROP part5aside');
  }
}
