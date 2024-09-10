<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211021214339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table personnage.';
    }

    public function up(Schema $schema): void
    {
        // Table déjà en place en prod : cette migration a été découpée après coup.
        if ($this->connection->createSchemaManager()->tablesExist(['personnage'])) {
            return;
        }

        $this->addSql('CREATE TABLE personnage (id INT AUTO_INCREMENT NOT NULL, clan_id INT DEFAULT NULL, classe_id INT NOT NULL, ecole_id INT DEFAULT NULL, joueur_id INT DEFAULT NULL, prenom VARCHAR(255) NOT NULL, nom VARCHAR(255) DEFAULT NULL, titres VARCHAR(255) DEFAULT NULL, icone VARCHAR(255) NOT NULL, illustration VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, est_pj TINYINT(1) NOT NULL, INDEX IDX_6AEA486DBEAF84C8 (clan_id), INDEX IDX_6AEA486D8F5EA509 (classe_id), INDEX IDX_6AEA486D77EF1B1E (ecole_id), INDEX IDX_6AEA486DA9E2D76C (joueur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE personnage ADD CONSTRAINT FK_6AEA486DBEAF84C8 FOREIGN KEY (clan_id) REFERENCES clan (id)');
        $this->addSql('ALTER TABLE personnage ADD CONSTRAINT FK_6AEA486D8F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)');
        $this->addSql('ALTER TABLE personnage ADD CONSTRAINT FK_6AEA486D77EF1B1E FOREIGN KEY (ecole_id) REFERENCES ecole (id)');
        $this->addSql('ALTER TABLE personnage ADD CONSTRAINT FK_6AEA486DA9E2D76C FOREIGN KEY (joueur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE personnage');
    }
}
