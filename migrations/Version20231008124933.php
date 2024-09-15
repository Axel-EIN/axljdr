<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231008124933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE famille (id INT AUTO_INCREMENT NOT NULL, clan_id INT NOT NULL, chef_id INT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, mon VARCHAR(255) DEFAULT NULL, INDEX IDX_2473F213BEAF84C8 (clan_id), UNIQUE INDEX UNIQ_2473F213150A48F1 (chef_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE famille ADD CONSTRAINT FK_2473F213BEAF84C8 FOREIGN KEY (clan_id) REFERENCES clan (id)');
        $this->addSql('ALTER TABLE famille ADD CONSTRAINT FK_2473F213150A48F1 FOREIGN KEY (chef_id) REFERENCES personnage (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE famille DROP FOREIGN KEY FK_2473F213BEAF84C8');
        $this->addSql('ALTER TABLE famille DROP FOREIGN KEY FK_2473F213150A48F1');
        $this->addSql('DROP TABLE famille');
    }
}
