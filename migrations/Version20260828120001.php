<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260828120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Crée la table character_unlock : ce qu\'un personnage a débloqué, toutes entités confondues.';
  }

  public function up(Schema $schema): void
  {
    if ($this->connection->createSchemaManager()->tablesExist(['character_unlock'])) {
      return;
    }

    $this->addSql('CREATE TABLE character_unlock (id INT AUTO_INCREMENT NOT NULL, character_id INT NOT NULL, entity VARCHAR(32) NOT NULL, element_id INT NOT NULL, unlocked_at DATETIME NOT NULL, INDEX IDX_C9A6737C1136BE75 (character_id), UNIQUE INDEX unlock_element_character (character_id, entity, element_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE character_unlock ADD CONSTRAINT FK_C9A6737C1136BE75 FOREIGN KEY (character_id) REFERENCES personnage (id) ON DELETE CASCADE');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE character_unlock');
  }
}
