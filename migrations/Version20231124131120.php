<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231124131120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lore (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, pdf VARCHAR(255) DEFAULT NULL, part1 LONGTEXT DEFAULT NULL, part1titre VARCHAR(255) DEFAULT NULL, part1aside LONGTEXT DEFAULT NULL, part2 LONGTEXT DEFAULT NULL, part2titre VARCHAR(255) DEFAULT NULL, part2aside LONGTEXT DEFAULT NULL, part3 LONGTEXT DEFAULT NULL, part3titre VARCHAR(255) DEFAULT NULL, part3aside LONGTEXT DEFAULT NULL, numero INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE lore');
    }
}
