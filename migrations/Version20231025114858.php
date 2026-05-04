<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231025114858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule ADD part1aside LONGTEXT DEFAULT NULL, ADD part2aside LONGTEXT DEFAULT NULL, ADD part3aside LONGTEXT DEFAULT NULL, DROP part1illu, DROP part2illu, DROP part3illu');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule ADD part1illu VARCHAR(255) DEFAULT NULL, ADD part2illu VARCHAR(255) DEFAULT NULL, ADD part3illu VARCHAR(255) DEFAULT NULL, DROP part1aside, DROP part2aside, DROP part3aside');
    }
}
