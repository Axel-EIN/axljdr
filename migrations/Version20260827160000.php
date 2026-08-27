<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260827160000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Crée la table de liaison known_spell : les sorts connus d\'un personnage.';
  }

  public function up(Schema $schema): void
  {
    if ($this->connection->createSchemaManager()->tablesExist(['known_spell'])) {
      return;
    }

    $this->addSql('CREATE TABLE known_spell (fiche_personnage_id INT NOT NULL, sort_id INT NOT NULL, INDEX IDX_C25FF5A58DE077C (fiche_personnage_id), INDEX IDX_C25FF5A547013001 (sort_id), PRIMARY KEY(fiche_personnage_id, sort_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE known_spell ADD CONSTRAINT FK_C25FF5A58DE077C FOREIGN KEY (fiche_personnage_id) REFERENCES fiche_personnage (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE known_spell ADD CONSTRAINT FK_C25FF5A547013001 FOREIGN KEY (sort_id) REFERENCES sort (id) ON DELETE CASCADE');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE known_spell');
  }
}
