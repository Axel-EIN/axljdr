<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260830120003 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Development : une ligne d\'arc narratif écrite par le MJ, accrochée à la participation du personnage dans une scène.';
  }

  public function up(Schema $schema): void
  {
    // Une table `development` a existé en prod puis a été droppée (Version20241102163541) ;
    // le garde évite l'échec si une base l'a encore.
    if ($this->connection->createSchemaManager()->tablesExist(['development'])) {
      return;
    }

    $this->addSql('CREATE TABLE development (id INT AUTO_INCREMENT NOT NULL, participation_id INT NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_C0D6212A6ACE3B73 (participation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE development ADD CONSTRAINT FK_C0D6212A6ACE3B73 FOREIGN KEY (participation_id) REFERENCES participation (id) ON DELETE CASCADE');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DROP TABLE development');
  }
}
