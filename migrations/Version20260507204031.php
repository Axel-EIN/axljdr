<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507204031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage ADD armure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_personnage ADD CONSTRAINT FK_C4BC4C9AE4000E4F FOREIGN KEY (armure_id) REFERENCES objet (id)');
        $this->addSql('CREATE INDEX IDX_C4BC4C9AE4000E4F ON fiche_personnage (armure_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage DROP FOREIGN KEY FK_C4BC4C9AE4000E4F');
        $this->addSql('DROP INDEX IDX_C4BC4C9AE4000E4F ON fiche_personnage');
        $this->addSql('ALTER TABLE fiche_personnage DROP armure_id');
    }
}
