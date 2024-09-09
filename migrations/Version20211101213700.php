<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211101213700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table archive.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE archive (id INT AUTO_INCREMENT NOT NULL, auteur_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, contenu LONGTEXT NOT NULL, INDEX IDX_D5FC5D9C60BB6FE6 (auteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE archive ADD CONSTRAINT FK_D5FC5D9C60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES personnage (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE archive');
    }
}
