<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231105013024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule ADD list_filter_fields VARCHAR(50) DEFAULT NULL, CHANGE liste list_entity VARCHAR(50) DEFAULT NULL, CHANGE liste_intro list_intro LONGTEXT DEFAULT NULL, CHANGE liste_onglets list_tab_fields VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule ADD liste VARCHAR(50) DEFAULT NULL, DROP list_entity, DROP list_filter_fields, CHANGE list_intro liste_intro LONGTEXT DEFAULT NULL, CHANGE list_tab_fields liste_onglets VARCHAR(255) DEFAULT NULL');
    }
}
