<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211021214337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table classe.';
    }

    public function up(Schema $schema): void
    {
        // Table déjà en place en prod : cette migration a été découpée après coup.
        if ($this->connection->createSchemaManager()->tablesExist(['classe'])) {
            return;
        }

        $this->addSql('CREATE TABLE classe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE classe');
    }
}
