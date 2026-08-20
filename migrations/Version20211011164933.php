<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211011164933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table saison.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE saison (id INT AUTO_INCREMENT NOT NULL, numero INT NOT NULL, titre VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, couleur VARCHAR(7) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE saison');
    }
}
