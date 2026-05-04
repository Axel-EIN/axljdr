<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231031222003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avantage ADD exclusive_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE avantage ADD CONSTRAINT FK_A95D71E5FAE3A6AE FOREIGN KEY (exclusive_id) REFERENCES classe (id)');
        $this->addSql('CREATE INDEX IDX_A95D71E5FAE3A6AE ON avantage (exclusive_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avantage DROP FOREIGN KEY FK_A95D71E5FAE3A6AE');
        $this->addSql('DROP INDEX IDX_A95D71E5FAE3A6AE ON avantage');
        $this->addSql('ALTER TABLE avantage DROP exclusive_id');
    }
}
