<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025200001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'scene.texte : les liens de personnages écrits par le Baliseur passent de personnages/profil/ à characters/profile/, suite au renommage de la rubrique.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE scene SET texte = REPLACE(texte, '../../personnages/profil/', '../../characters/profile/') WHERE texte LIKE '%../../personnages/profil/%'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE scene SET texte = REPLACE(texte, '../../characters/profile/', '../../personnages/profil/') WHERE texte LIKE '%../../characters/profile/%'");
  }
}
