<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905120004 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Library reçoit le palier access et ses dates : les bibliothèques existantes deviennent publiques, datées du passé.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE library ADD access SMALLINT DEFAULT NULL, ADD published_at DATETIME DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL');
    $this->addSql('UPDATE library SET access = 3, published_at = \'2020-01-01 00:00:00\', created_at = NOW()');
    $this->addSql('ALTER TABLE library MODIFY access SMALLINT NOT NULL, MODIFY created_at DATETIME NOT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DELETE FROM character_unlock WHERE entity = \'library\'');
    $this->addSql('ALTER TABLE library DROP access, DROP published_at, DROP created_at');
  }
}
