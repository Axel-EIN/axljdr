<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231031222824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avantage ADD discount_clan2_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE avantage ADD CONSTRAINT FK_A95D71E53BDE4FE4 FOREIGN KEY (discount_clan2_id) REFERENCES clan (id)');
        $this->addSql('CREATE INDEX IDX_A95D71E53BDE4FE4 ON avantage (discount_clan2_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avantage DROP FOREIGN KEY FK_A95D71E53BDE4FE4');
        $this->addSql('DROP INDEX IDX_A95D71E53BDE4FE4 ON avantage');
        $this->addSql('ALTER TABLE avantage DROP discount_clan2_id');
    }
}
