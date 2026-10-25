<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025132300 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'La fiche perd Arme1 et Arme2 : ces armes rejoignent l\'inventaire, et Arme1 devient l\'arme actuelle quand la fiche n\'en a pas.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('INSERT IGNORE INTO inventory_item (fiche_personnage_id, objet_id) SELECT id, arme_id FROM fiche_personnage WHERE arme_id IS NOT NULL');
    $this->addSql('INSERT IGNORE INTO inventory_item (fiche_personnage_id, objet_id) SELECT id, arme2_id FROM fiche_personnage WHERE arme2_id IS NOT NULL');
    $this->addSql('UPDATE fiche_personnage SET arme_actuelle_id = arme_id WHERE arme_actuelle_id IS NULL AND arme_id IS NOT NULL');
    $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A_ARME2');
    $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A21D9C0A');
    $this->addSql('DROP INDEX IDX_C4BC4C9A21D9C0A ON fiche_personnage');
    $this->addSql('DROP INDEX IDX_C4BC4C9A240E76FF ON fiche_personnage');
    $this->addSql('ALTER TABLE fiche_personnage DROP arme_id, DROP arme2_id');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fiche_personnage ADD arme_id INT DEFAULT NULL, ADD arme2_id INT DEFAULT NULL');
    $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A_ARME2 FOREIGN KEY (arme2_id) REFERENCES objet (id)');
    $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A21D9C0A FOREIGN KEY (arme_id) REFERENCES objet (id)');
    $this->addSql('CREATE INDEX IDX_C4BC4C9A21D9C0A ON fiche_personnage (arme_id)');
    $this->addSql('CREATE INDEX IDX_C4BC4C9A240E76FF ON fiche_personnage (arme2_id)');
  }
}
