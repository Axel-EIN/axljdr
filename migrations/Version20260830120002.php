<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260830120002 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Personnage reçoit ses deux zones de notes : celles que le joueur adresse au MJ, et celles que le MJ garde pour lui.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE personnage ADD player_notes LONGTEXT DEFAULT NULL, ADD gm_notes LONGTEXT DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE personnage DROP player_notes, DROP gm_notes');
  }
}
