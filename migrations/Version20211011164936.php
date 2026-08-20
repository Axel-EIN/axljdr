<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211011164936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table scene.';
    }

    public function up(Schema $schema): void
    {
        // Table déjà en place en prod : cette migration a été découpée après coup.
        if ($this->connection->createSchemaManager()->tablesExist(['scene'])) {
            return;
        }

        $this->addSql('CREATE TABLE scene (id INT AUTO_INCREMENT NOT NULL, episode_parent_id INT NOT NULL, numero SMALLINT NOT NULL, titre VARCHAR(255) NOT NULL, temps VARCHAR(255) NOT NULL, texte LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, INDEX IDX_D979EFDACCB85C49 (episode_parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE scene ADD CONSTRAINT FK_D979EFDACCB85C49 FOREIGN KEY (episode_parent_id) REFERENCES episode (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE scene');
    }
}
