<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231115202553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE objet DROP malus, DROP portee_arc, DROP prise, DROP nd_armure_cheval');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE objet ADD malus LONGTEXT DEFAULT NULL, ADD portee_arc INT DEFAULT NULL, ADD prise TINYINT(1) DEFAULT NULL, ADD nd_armure_cheval INT DEFAULT NULL');
    }
}
