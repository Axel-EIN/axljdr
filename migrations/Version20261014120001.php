<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'scene.texte : les liens de lieux écrits par le Baliseur passent de empire/lieu/ à world/lieu/, suite au renommage de la rubrique.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql("UPDATE scene SET texte = REPLACE(texte, '../../empire/lieu/', '../../world/lieu/') WHERE texte LIKE '%../../empire/lieu/%'");
  }

  public function down(Schema $schema): void
  {
    $this->addSql("UPDATE scene SET texte = REPLACE(texte, '../../world/lieu/', '../../empire/lieu/') WHERE texte LIKE '%../../world/lieu/%'");
  }
}
