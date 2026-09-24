<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924120004 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Inventaire du personnage : table de liaison entre la fiche et les objets qu'elle possède, sur le modèle des sorts connus.";
  }

  public function up(Schema $schema): void
  {
    if ($this->connection->createSchemaManager()->tablesExist(['inventory_item'])) {
      return;
    }

    $this->addSql('CREATE TABLE inventory_item (fiche_personnage_id INT NOT NULL, objet_id INT NOT NULL, INDEX IDX_55BDEA308DE077C (fiche_personnage_id), INDEX IDX_55BDEA30F520CF5A (objet_id), PRIMARY KEY(fiche_personnage_id, objet_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE inventory_item ADD CONSTRAINT FK_55BDEA308DE077C FOREIGN KEY (fiche_personnage_id) REFERENCES fiche_personnage (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE inventory_item ADD CONSTRAINT FK_55BDEA30F520CF5A FOREIGN KEY (objet_id) REFERENCES objet (id) ON DELETE CASCADE');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE inventory_item');
  }
}
