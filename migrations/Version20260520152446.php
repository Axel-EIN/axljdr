<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520152446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage ADD initiative_modifier VARCHAR(10) DEFAULT NULL, ADD attaque_modifier VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_personnage RENAME INDEX idx_c4bc4c9a_arme2 TO IDX_C4BC4C9A240E76FF');
        $this->addSql('ALTER TABLE fiche_personnage RENAME INDEX idx_c4bc4c9a_armeact TO IDX_C4BC4C9A3EABB9A6');
        $this->addSql('ALTER TABLE fiche_personnage RENAME INDEX idx_c4bc4c9a_compcbt TO IDX_C4BC4C9A4ED07CD4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_personnage DROP initiative_modifier, DROP attaque_modifier');
        $this->addSql('ALTER TABLE fiche_personnage RENAME INDEX idx_c4bc4c9a4ed07cd4 TO IDX_C4BC4C9A_COMPCBT');
        $this->addSql('ALTER TABLE fiche_personnage RENAME INDEX idx_c4bc4c9a240e76ff TO IDX_C4BC4C9A_ARME2');
        $this->addSql('ALTER TABLE fiche_personnage RENAME INDEX idx_c4bc4c9a3eabb9a6 TO IDX_C4BC4C9A_ARMEACT');
    }
}
