<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231119181148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE library (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, base TINYINT(1) DEFAULT NULL, numero INT NOT NULL, image VARCHAR(255) DEFAULT NULL, entity VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, tab_field VARCHAR(255) DEFAULT NULL, sub_tab_field VARCHAR(255) DEFAULT NULL, filter_field VARCHAR(255) DEFAULT NULL, pdf VARCHAR(255) DEFAULT NULL, description_aside LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE library');
    }
}
