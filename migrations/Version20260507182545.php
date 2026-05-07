<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507182545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage ADD avantage1_id INT DEFAULT NULL, ADD avantage2_id INT DEFAULT NULL, ADD desavantage1_id INT DEFAULT NULL, ADD desavantage2_id INT DEFAULT NULL, DROP avantages, DROP desavantages');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AE45ED7C2 FOREIGN KEY (avantage1_id) REFERENCES avantage (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AF6EB782C FOREIGN KEY (avantage2_id) REFERENCES avantage (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A8F5AC3C4 FOREIGN KEY (desavantage1_id) REFERENCES avantage (id)');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9A9DEF6C2A FOREIGN KEY (desavantage2_id) REFERENCES avantage (id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AE45ED7C2 ON fiche_personnage (avantage1_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AF6EB782C ON fiche_personnage (avantage2_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A8F5AC3C4 ON fiche_personnage (desavantage1_id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9A9DEF6C2A ON fiche_personnage (desavantage2_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AE45ED7C2');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AF6EB782C');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A8F5AC3C4');
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9A9DEF6C2A');
        $this->addSql('DROP INDEX IDX_C4BC4C9AE45ED7C2 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9AF6EB782C ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A8F5AC3C4 ON fiche_personnage');
        $this->addSql('DROP INDEX IDX_C4BC4C9A9DEF6C2A ON fiche_personnage');
        $this->addSql('ALTER TABLE fiche_personnage ADD avantages LONGTEXT DEFAULT NULL, ADD desavantages LONGTEXT DEFAULT NULL, DROP avantage1_id, DROP avantage2_id, DROP desavantage1_id, DROP desavantage2_id');
    }
}
