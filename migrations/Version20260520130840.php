<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520130840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute comp_combat_actuelle_id, arme2_id et arme_actuelle_id sur fiche_personnage.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fiche_personnage ADD comp_combat_actuelle_id INT DEFAULT NULL, ADD arme2_id INT DEFAULT NULL, ADD arme_actuelle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A_COMPCBT FOREIGN KEY (comp_combat_actuelle_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A_ARME2 FOREIGN KEY (arme2_id) REFERENCES objet (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A_ARMEACT FOREIGN KEY (arme_actuelle_id) REFERENCES objet (id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A_COMPCBT ON fiche_personnage (comp_combat_actuelle_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A_ARME2 ON fiche_personnage (arme2_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A_ARMEACT ON fiche_personnage (arme_actuelle_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A_COMPCBT');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A_ARME2');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A_ARMEACT');
        $this->addSql('DROP INDEX IDX_C4BC4C9A_COMPCBT ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A_ARME2 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A_ARMEACT ON fiche_personnage');
        $this->addSql('ALTER TABLE fiche_personnage DROP comp_combat_actuelle_id, DROP arme2_id, DROP arme_actuelle_id');
    }
}
