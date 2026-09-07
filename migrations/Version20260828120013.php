<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260828120013 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Avantage reçoit le palier access et ses dates : tout ce qui existe devient public, daté du passé pour ne pas ressortir en nouveauté.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE avantage ADD access SMALLINT DEFAULT NULL, ADD published_at DATETIME DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL');
    $this->addSql('UPDATE avantage SET access = 3, published_at = \'2020-01-01 00:00:00\', created_at = NOW()');
    $this->addSql('ALTER TABLE avantage MODIFY access SMALLINT NOT NULL, MODIFY created_at DATETIME NOT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DELETE FROM character_unlock WHERE entity = \'avantage\'');
    $this->addSql('ALTER TABLE avantage DROP access, DROP published_at, DROP created_at');
  }
}
