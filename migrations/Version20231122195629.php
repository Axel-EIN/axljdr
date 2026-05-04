<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231122195629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule DROP list_entity, DROP list_intro, DROP list_tab_field, DROP list_filter_field');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule ADD list_entity VARCHAR(50) DEFAULT NULL, ADD list_intro LONGTEXT DEFAULT NULL, ADD list_tab_field VARCHAR(255) DEFAULT NULL, ADD list_filter_field VARCHAR(50) DEFAULT NULL');
    }
}
