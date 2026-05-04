<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231031215454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avantage ADD discount_clan_id INT DEFAULT NULL, ADD discount_classe_id INT DEFAULT NULL, ADD discount INT DEFAULT NULL');
        $this->addSql('ALTER TABLE avantage ADD CONSTRAINT FK_A95D71E58A7E9D9 FOREIGN KEY (discount_clan_id) REFERENCES clan (id)');
        $this->addSql('ALTER TABLE avantage ADD CONSTRAINT FK_A95D71E5EF842DF4 FOREIGN KEY (discount_classe_id) REFERENCES classe (id)');
        $this->addSql('CREATE INDEX IDX_A95D71E58A7E9D9 ON avantage (discount_clan_id)');
        $this->addSql('CREATE INDEX IDX_A95D71E5EF842DF4 ON avantage (discount_classe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avantage DROP FOREIGN KEY FK_A95D71E58A7E9D9');
        $this->addSql('ALTER TABLE avantage DROP FOREIGN KEY FK_A95D71E5EF842DF4');
        $this->addSql('DROP INDEX IDX_A95D71E58A7E9D9 ON avantage');
        $this->addSql('DROP INDEX IDX_A95D71E5EF842DF4 ON avantage');
        $this->addSql('ALTER TABLE avantage DROP discount_clan_id, DROP discount_classe_id, DROP discount');
    }
}
