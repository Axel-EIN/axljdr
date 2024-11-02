<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241102135851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE development (id INT AUTO_INCREMENT NOT NULL, protagonist_id INT NOT NULL, scene_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, resume LONGTEXT NOT NULL, INDEX IDX_C0D6212ACDF038AD (protagonist_id), INDEX IDX_C0D6212A166053B4 (scene_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE development ADD CONSTRAINT FK_C0D6212ACDF038AD FOREIGN KEY (protagonist_id) REFERENCES personnage (id)');
        $this->addSql('ALTER TABLE development ADD CONSTRAINT FK_C0D6212A166053B4 FOREIGN KEY (scene_id) REFERENCES scene (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE development DROP FOREIGN KEY FK_C0D6212ACDF038AD');
        $this->addSql('ALTER TABLE development DROP FOREIGN KEY FK_C0D6212A166053B4');
        $this->addSql('DROP TABLE development');
    }
}
