<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231123153352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sort (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, categorie VARCHAR(60) NOT NULL, anneau VARCHAR(60) DEFAULT NULL, niveau SMALLINT DEFAULT NULL, portee VARCHAR(255) DEFAULT NULL, zone VARCHAR(255) DEFAULT NULL, duree VARCHAR(255) DEFAULT NULL, augmentations VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, mot_cle1 VARCHAR(60) DEFAULT NULL, mot_cle2 VARCHAR(60) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sort');
    }
}
