<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507153647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clan DROP bonus_stat_nom');
        $this->addSql('ALTER TABLE famille ADD bonus_stat_nom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_personnage DROP bonus_famille, DROP bonus_ecole');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE famille DROP bonus_stat_nom');
        $this->addSql('ALTER TABLE clan ADD bonus_stat_nom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_personnage ADD bonus_famille SMALLINT DEFAULT NULL, ADD bonus_ecole SMALLINT DEFAULT NULL');
    }
}
