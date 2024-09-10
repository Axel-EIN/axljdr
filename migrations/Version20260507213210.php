<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507213210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage ADD competence16_id INT DEFAULT NULL, ADD competence17_id INT DEFAULT NULL, ADD competence18_id INT DEFAULT NULL, ADD competence19_id INT DEFAULT NULL, ADD competence20_id INT DEFAULT NULL, ADD valeur16 SMALLINT DEFAULT NULL, ADD valeur17 SMALLINT DEFAULT NULL, ADD valeur18 SMALLINT DEFAULT NULL, ADD valeur19 SMALLINT DEFAULT NULL, ADD valeur20 SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AC9EA774 FOREIGN KEY (competence16_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AB422C011 FOREIGN KEY (competence17_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AEC41D047 FOREIGN KEY (competence18_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A54FDB722 FOREIGN KEY (competence19_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A6E558278 FOREIGN KEY (competence20_id) REFERENCES competence (id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AC9EA774 ON fiche_personnage (competence16_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AB422C011 ON fiche_personnage (competence17_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AEC41D047 ON fiche_personnage (competence18_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A54FDB722 ON fiche_personnage (competence19_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A6E558278 ON fiche_personnage (competence20_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AC9EA774');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AB422C011');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AEC41D047');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A54FDB722');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A6E558278');
        $this->addSql('DROP INDEX IDX_C4BC4C9AC9EA774 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9AB422C011 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9AEC41D047 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A54FDB722 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A6E558278 ON fiche_personnage');
        $this->addSql('ALTER TABLE fiche_personnage DROP competence16_id, DROP competence17_id, DROP competence18_id, DROP competence19_id, DROP competence20_id, DROP valeur16, DROP valeur17, DROP valeur18, DROP valeur19, DROP valeur20');
    }
}
