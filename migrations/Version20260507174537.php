<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507174537 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage ADD competence11_id INT DEFAULT NULL, ADD competence12_id INT DEFAULT NULL, ADD competence13_id INT DEFAULT NULL, ADD competence14_id INT DEFAULT NULL, ADD competence15_id INT DEFAULT NULL, ADD valeur11 SMALLINT DEFAULT NULL, ADD valeur12 SMALLINT DEFAULT NULL, ADD valeur13 SMALLINT DEFAULT NULL, ADD valeur14 SMALLINT DEFAULT NULL, ADD valeur15 SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A91499FCD FOREIGN KEY (competence11_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A83FC3023 FOREIGN KEY (competence12_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A3B405746 FOREIGN KEY (competence13_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AA6976FFF FOREIGN KEY (competence14_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A1E2B089A FOREIGN KEY (competence15_id) REFERENCES competence (id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A91499FCD ON fiche_personnage (competence11_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A83FC3023 ON fiche_personnage (competence12_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A3B405746 ON fiche_personnage (competence13_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AA6976FFF ON fiche_personnage (competence14_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A1E2B089A ON fiche_personnage (competence15_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A91499FCD');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A83FC3023');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A3B405746');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AA6976FFF');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A1E2B089A');
        $this->addSql('DROP INDEX IDX_C4BC4C9A91499FCD ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A83FC3023 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A3B405746 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9AA6976FFF ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A1E2B089A ON fiche_personnage');
        $this->addSql('ALTER TABLE fiche_personnage DROP competence11_id, DROP competence12_id, DROP competence13_id, DROP competence14_id, DROP competence15_id, DROP valeur11, DROP valeur12, DROP valeur13, DROP valeur14, DROP valeur15');
    }
}
