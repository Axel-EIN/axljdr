<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260828120008 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Personnage passe de locked au palier access, avec ses dates ; ce qui existe est daté du passé pour ne pas ressortir en nouveauté.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE personnage ADD access SMALLINT DEFAULT NULL, ADD published_at DATETIME DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL');
    $this->addSql('UPDATE personnage SET access = IF(locked = 1, 1, 3), published_at = \'2020-01-01 00:00:00\', created_at = NOW()');
    $this->addSql('ALTER TABLE personnage MODIFY access SMALLINT NOT NULL, MODIFY created_at DATETIME NOT NULL');
    $this->addSql('ALTER TABLE personnage DROP locked');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DELETE FROM character_unlock WHERE entity = \'personnage\'');
    $this->addSql('ALTER TABLE personnage ADD locked TINYINT(1) DEFAULT NULL');
    $this->addSql('UPDATE personnage SET locked = IF(access = 3, 0, 1)');
    $this->addSql('ALTER TABLE personnage MODIFY locked TINYINT(1) NOT NULL');
    $this->addSql('ALTER TABLE personnage DROP access, DROP published_at, DROP created_at');
  }
}
