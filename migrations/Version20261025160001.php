<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025160001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'scene.texte : les liens de lieux écrits par le Baliseur passent de world/lieu/ à world/location/, suite au renommage de la route.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE scene SET texte = REPLACE(texte, '../../world/lieu/', '../../world/location/') WHERE texte LIKE '%../../world/lieu/%'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE scene SET texte = REPLACE(texte, '../../world/location/', '../../world/lieu/') WHERE texte LIKE '%../../world/location/%'");
  }
}
