<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260907120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Crée la table de liaison found_object : les objets découverts au cours d\'une scène.';
  }

  public function up(Schema $schema): void
  {
    if ($this->connection->createSchemaManager()->tablesExist(['found_object'])) {
      return;
    }

    $this->addSql('CREATE TABLE found_object (scene_id INT NOT NULL, objet_id INT NOT NULL, INDEX IDX_630444C3166053B4 (scene_id), INDEX IDX_630444C3F520CF5A (objet_id), PRIMARY KEY(scene_id, objet_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE found_object ADD CONSTRAINT FK_630444C3166053B4 FOREIGN KEY (scene_id) REFERENCES scene (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE found_object ADD CONSTRAINT FK_630444C3F520CF5A FOREIGN KEY (objet_id) REFERENCES objet (id) ON DELETE CASCADE');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE found_object');
  }
}
