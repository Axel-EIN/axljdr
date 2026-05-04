<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211021214338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table ecole.';
    }

    public function up(Schema $schema): void
    {
        // Table déjà en place en prod : cette migration a été découpée après coup.
        if ($this->connection->createSchemaManager()->tablesExist(['ecole'])) {
            return;
        }

        $this->addSql('CREATE TABLE ecole (id INT AUTO_INCREMENT NOT NULL, classe_id INT NOT NULL, clan_id INT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, tech1_nom VARCHAR(255) DEFAULT NULL, tech1_desc LONGTEXT DEFAULT NULL, tech2_nom VARCHAR(255) DEFAULT NULL, tech2_desc LONGTEXT DEFAULT NULL, tech3_nom VARCHAR(255) DEFAULT NULL, tech3_desc LONGTEXT DEFAULT NULL, tech4_nom VARCHAR(255) DEFAULT NULL, tech4_desc LONGTEXT DEFAULT NULL, tech5_nom VARCHAR(255) DEFAULT NULL, tech5_desc LONGTEXT DEFAULT NULL, tech_special_nom VARCHAR(255) DEFAULT NULL, tech_special_desc LONGTEXT DEFAULT NULL, INDEX IDX_9786AAC8F5EA509 (classe_id), INDEX IDX_9786AACBEAF84C8 (clan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ecole ADD CONSTRAINT FK_9786AAC8F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)');
        $this->addSql('ALTER TABLE ecole ADD CONSTRAINT FK_9786AACBEAF84C8 FOREIGN KEY (clan_id) REFERENCES clan (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ecole');
    }
}
