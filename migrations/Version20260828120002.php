<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260828120002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Lieu passe de locked au palier access, avec ses dates ; les lieux à débloquer sont ouverts aux PJ qui y ont déjà joué.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu ADD access SMALLINT DEFAULT NULL, ADD published_at DATETIME DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL');
    $this->addSql('UPDATE lieu SET access = IF(locked = 1, 1, 3), published_at = \'2020-01-01 00:00:00\', created_at = NOW()');
    $this->addSql('ALTER TABLE lieu MODIFY access SMALLINT NOT NULL, MODIFY created_at DATETIME NOT NULL');
    $this->addSql('ALTER TABLE lieu DROP locked');

    $this->addSql('INSERT IGNORE INTO character_unlock (character_id, entity, element_id, unlocked_at)
      SELECT DISTINCT p.personnage_id, \'lieu\', s.lieu_id, NOW()
      FROM participation p
      JOIN scene s ON s.id = p.scene_id
      JOIN lieu l ON l.id = s.lieu_id
      WHERE p.est_pj = 1 AND l.access = 1');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DELETE FROM character_unlock WHERE entity = \'lieu\'');
    $this->addSql('ALTER TABLE lieu ADD locked TINYINT(1) DEFAULT NULL');
    $this->addSql('UPDATE lieu SET locked = IF(access = 3, 0, 1)');
    $this->addSql('ALTER TABLE lieu MODIFY locked TINYINT(1) NOT NULL');
    $this->addSql('ALTER TABLE lieu DROP access, DROP published_at, DROP created_at');
  }
}
