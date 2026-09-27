<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260928090000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Seconde classe bénéficiant du coût réduit d'un avantage ou d'un désavantage.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE avantage ADD discount_classe2_id INT DEFAULT NULL');
    $this->addSql('ALTER TABLE avantage ADD CONSTRAINT FK_A95D71E57EE63055 FOREIGN KEY (discount_classe2_id) REFERENCES classe (id)');
    $this->addSql('CREATE INDEX IDX_A95D71E57EE63055 ON avantage (discount_classe2_id)');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE avantage DROP FOREIGN KEY FK_A95D71E57EE63055');
    $this->addSql('DROP INDEX IDX_A95D71E57EE63055 ON avantage');
    $this->addSql('ALTER TABLE avantage DROP discount_classe2_id');
  }
}
