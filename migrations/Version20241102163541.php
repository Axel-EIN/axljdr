<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241102163541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE development DROP FOREIGN KEY FK_C0D6212A166053B4');
        $this->addSql('ALTER TABLE development DROP FOREIGN KEY FK_C0D6212ACDF038AD');
        $this->addSql('DROP TABLE development');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE development (id INT AUTO_INCREMENT NOT NULL, protagonist_id INT NOT NULL, scene_id INT DEFAULT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, resume LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_C0D6212ACDF038AD (protagonist_id), INDEX IDX_C0D6212A166053B4 (scene_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE development ADD CONSTRAINT FK_C0D6212A166053B4 FOREIGN KEY (scene_id) REFERENCES scene (id)');
        $this->addSql('ALTER TABLE development ADD CONSTRAINT FK_C0D6212ACDF038AD FOREIGN KEY (protagonist_id) REFERENCES personnage (id)');
    }
}
