<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211101213701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table lieu.';
    }

    public function up(Schema $schema): void
    {
        // La prod a déjà `lieu` : elle date d'avant la découpe de cette migration.
        // Test par la connexion — toucher $schema déclenche un diff automatique.
        if ($this->connection->createSchemaManager()->tablesExist(['lieu'])) {
            return;
        }

        $this->addSql('CREATE TABLE lieu (id INT AUTO_INCREMENT NOT NULL, clan_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, carte VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, coordinates VARCHAR(255) DEFAULT NULL, INDEX IDX_2F577D59BEAF84C8 (clan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lieu ADD CONSTRAINT FK_2F577D59BEAF84C8 FOREIGN KEY (clan_id) REFERENCES clan (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lieu');
    }
}
