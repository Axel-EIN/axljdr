<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905120003 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'character_unlock distingue le débloquage par rencontre de celui que le MJ accorde : un changement de palier ne balaie que le premier.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE character_unlock ADD by_meeting TINYINT(1) DEFAULT 0 NOT NULL');
    $this->addSql('ALTER TABLE character_unlock ALTER by_meeting DROP DEFAULT');

    $this->addSql('UPDATE character_unlock cu
      JOIN personnage croise ON croise.id = cu.element_id AND croise.access = 2
      JOIN participation moi ON moi.personnage_id = cu.character_id AND moi.est_pj = 1
      JOIN participation autre ON autre.scene_id = moi.scene_id AND autre.personnage_id = cu.element_id
      SET cu.by_meeting = 1
      WHERE cu.entity = \'personnage\'');

    $this->addSql('UPDATE character_unlock cu
      JOIN lieu l ON l.id = cu.element_id AND l.access = 2
      JOIN participation p ON p.personnage_id = cu.character_id AND p.est_pj = 1
      JOIN scene s ON s.id = p.scene_id AND s.lieu_id = cu.element_id
      SET cu.by_meeting = 1
      WHERE cu.entity = \'lieu\'');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE character_unlock DROP by_meeting');
  }
}
