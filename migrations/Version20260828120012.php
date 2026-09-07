<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260828120012 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Competence reçoit le palier access et ses dates : tout ce qui existe devient public, daté du passé pour ne pas ressortir en nouveauté.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE competence ADD access SMALLINT DEFAULT NULL, ADD published_at DATETIME DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL');
    $this->addSql('UPDATE competence SET access = 3, published_at = \'2020-01-01 00:00:00\', created_at = NOW()');
    $this->addSql('ALTER TABLE competence MODIFY access SMALLINT NOT NULL, MODIFY created_at DATETIME NOT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DELETE FROM character_unlock WHERE entity = \'competence\'');
    $this->addSql('ALTER TABLE competence DROP access, DROP published_at, DROP created_at');
  }
}
