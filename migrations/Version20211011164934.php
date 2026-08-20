<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211011164934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table chapitre.';
    }

    public function up(Schema $schema): void
    {
        // Table déjà en place en prod : cette migration a été découpée après coup.
        if ($this->connection->createSchemaManager()->tablesExist(['chapitre'])) {
            return;
        }

        $this->addSql('CREATE TABLE chapitre (id INT AUTO_INCREMENT NOT NULL, saison_parent_id INT NOT NULL, numero INT NOT NULL, titre VARCHAR(255) NOT NULL, citation VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, couleur VARCHAR(7) NOT NULL, INDEX IDX_8C62B025406DFFCA (saison_parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chapitre ADD CONSTRAINT FK_8C62B025406DFFCA FOREIGN KEY (saison_parent_id) REFERENCES saison (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE chapitre');
    }
}
