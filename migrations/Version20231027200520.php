<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027200520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule ADD part4 LONGTEXT DEFAULT NULL, ADD part4titre VARCHAR(255) DEFAULT NULL, ADD part4aside LONGTEXT DEFAULT NULL, ADD part5 LONGTEXT DEFAULT NULL, ADD part5titre VARCHAR(255) DEFAULT NULL, ADD part5aside LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule DROP part4, DROP part4titre, DROP part4aside, DROP part5, DROP part5titre, DROP part5aside');
    }
}
