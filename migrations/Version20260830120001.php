<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260830120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Utilisateur reçoit son personnage principal : les joueurs qui n\'en ont qu\'un le prennent d\'office.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE utilisateur ADD main_character_id INT DEFAULT NULL');
    $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B388E0BCC FOREIGN KEY (main_character_id) REFERENCES personnage (id) ON DELETE SET NULL');
    $this->addSql('CREATE INDEX IDX_1D1C63B388E0BCC ON utilisateur (main_character_id)');
    $this->addSql('UPDATE utilisateur u SET u.main_character_id = (SELECT MIN(p.id) FROM personnage p WHERE p.joueur_id = u.id) WHERE (SELECT COUNT(*) FROM personnage p WHERE p.joueur_id = u.id) = 1');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B388E0BCC');
    $this->addSql('DROP INDEX IDX_1D1C63B388E0BCC ON utilisateur');
    $this->addSql('ALTER TABLE utilisateur DROP main_character_id');
  }
}
