<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905120002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Sème les débloquages par participation : lieux et personnages au palier Joueurs que les PJ ont déjà croisés en scène.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('INSERT IGNORE INTO character_unlock (character_id, entity, element_id, unlocked_at)
      SELECT DISTINCT p.personnage_id, \'lieu\', s.lieu_id, NOW()
      FROM participation p
      JOIN scene s ON s.id = p.scene_id
      JOIN lieu l ON l.id = s.lieu_id
      WHERE p.est_pj = 1 AND l.access = 2');

    $this->addSql('INSERT IGNORE INTO character_unlock (character_id, entity, element_id, unlocked_at)
      SELECT DISTINCT moi.personnage_id, \'personnage\', autre.personnage_id, NOW()
      FROM participation moi
      JOIN participation autre ON autre.scene_id = moi.scene_id AND autre.personnage_id <> moi.personnage_id
      JOIN personnage croise ON croise.id = autre.personnage_id
      WHERE moi.est_pj = 1 AND croise.access = 2');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('DELETE cu FROM character_unlock cu
      JOIN lieu l ON l.id = cu.element_id
      WHERE cu.entity = \'lieu\' AND l.access = 2');

    $this->addSql('DELETE cu FROM character_unlock cu
      JOIN personnage p ON p.id = cu.element_id
      WHERE cu.entity = \'personnage\' AND p.access = 2');
  }
}
