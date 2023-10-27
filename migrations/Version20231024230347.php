<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231024230347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule ADD part1titre VARCHAR(255) DEFAULT NULL, ADD part1illu VARCHAR(255) DEFAULT NULL, ADD part2titre VARCHAR(255) DEFAULT NULL, ADD part2illu VARCHAR(255) DEFAULT NULL, ADD part3titre VARCHAR(255) DEFAULT NULL, ADD part3illu VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule DROP part1titre, DROP part1illu, DROP part2titre, DROP part2illu, DROP part3titre, DROP part3illu');
    }
}
