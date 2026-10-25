<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025200003 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'development.content : les liens de personnages écrits par le Baliseur passent de personnages/profil/ à characters/profile/, suite au renommage de la rubrique.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE development SET content = REPLACE(content, '../../personnages/profil/', '../../characters/profile/') WHERE content LIKE '%../../personnages/profil/%'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE development SET content = REPLACE(content, '../../characters/profile/', '../../personnages/profil/') WHERE content LIKE '%../../characters/profile/%'");
  }
}
