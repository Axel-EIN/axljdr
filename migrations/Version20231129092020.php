<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231129092020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sort ADD keyword1 VARCHAR(60) DEFAULT NULL, ADD keword2 VARCHAR(60) DEFAULT NULL, DROP mot_cle1, DROP mot_cle2, CHANGE mot_cle3 keword3 VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sort ADD mot_cle1 VARCHAR(60) DEFAULT NULL, ADD mot_cle2 VARCHAR(60) DEFAULT NULL, DROP keyword1, DROP keword2, CHANGE keword3 mot_cle3 VARCHAR(255) DEFAULT NULL');
    }
}
