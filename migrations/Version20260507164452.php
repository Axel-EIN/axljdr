<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507164452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage ADD competence1_id INT DEFAULT NULL, ADD competence2_id INT DEFAULT NULL, ADD competence3_id INT DEFAULT NULL, ADD competence4_id INT DEFAULT NULL, ADD competence5_id INT DEFAULT NULL, ADD competence6_id INT DEFAULT NULL, ADD competence7_id INT DEFAULT NULL, ADD competence8_id INT DEFAULT NULL, ADD competence9_id INT DEFAULT NULL, ADD competence10_id INT DEFAULT NULL, ADD valeur1 SMALLINT DEFAULT NULL, ADD valeur2 SMALLINT DEFAULT NULL, ADD valeur3 SMALLINT DEFAULT NULL, ADD valeur4 SMALLINT DEFAULT NULL, ADD valeur5 SMALLINT DEFAULT NULL, ADD valeur6 SMALLINT DEFAULT NULL, ADD valeur7 SMALLINT DEFAULT NULL, ADD valeur8 SMALLINT DEFAULT NULL, ADD valeur9 SMALLINT DEFAULT NULL, ADD valeur10 SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A977D21EE FOREIGN KEY (competence1_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A85C88E00 FOREIGN KEY (competence2_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A3D74E965 FOREIGN KEY (competence3_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AA0A3D1DC FOREIGN KEY (competence4_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A181FB6B9 FOREIGN KEY (competence5_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AAAA1957 FOREIGN KEY (competence6_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AB2167E32 FOREIGN KEY (competence7_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AEA756E64 FOREIGN KEY (competence8_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A52C90901 FOREIGN KEY (competence9_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A29F5F8A8 FOREIGN KEY (competence10_id) REFERENCES competence (id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A977D21EE ON fiche_personnage (competence1_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A85C88E00 ON fiche_personnage (competence2_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A3D74E965 ON fiche_personnage (competence3_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AA0A3D1DC ON fiche_personnage (competence4_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A181FB6B9 ON fiche_personnage (competence5_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AAAA1957 ON fiche_personnage (competence6_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AB2167E32 ON fiche_personnage (competence7_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AEA756E64 ON fiche_personnage (competence8_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A52C90901 ON fiche_personnage (competence9_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A29F5F8A8 ON fiche_personnage (competence10_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A977D21EE');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A85C88E00');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A3D74E965');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AA0A3D1DC');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A181FB6B9');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AAAA1957');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AB2167E32');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AEA756E64');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A52C90901');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A29F5F8A8');
        $this->addSql('DROP INDEX IDX_C4BC4C9A977D21EE ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A85C88E00 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A3D74E965 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9AA0A3D1DC ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A181FB6B9 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9AAAA1957 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9AB2167E32 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9AEA756E64 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A52C90901 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A29F5F8A8 ON fiche_personnage');
        $this->addSql('ALTER TABLE fiche_personnage DROP competence1_id, DROP competence2_id, DROP competence3_id, DROP competence4_id, DROP competence5_id, DROP competence6_id, DROP competence7_id, DROP competence8_id, DROP competence9_id, DROP competence10_id, DROP valeur1, DROP valeur2, DROP valeur3, DROP valeur4, DROP valeur5, DROP valeur6, DROP valeur7, DROP valeur8, DROP valeur9, DROP valeur10');
    }
}
