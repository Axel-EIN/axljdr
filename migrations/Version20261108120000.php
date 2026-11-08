<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108120000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'utilisateur : les rôles ROLE_MJ et ROLE_JOUEUR deviennent ROLE_GM et ROLE_PLAYER.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('UPDATE utilisateur SET roles = REPLACE(REPLACE(roles, \'"ROLE_MJ"\', \'"ROLE_GM"\'), \'"ROLE_JOUEUR"\', \'"ROLE_PLAYER"\')');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('UPDATE utilisateur SET roles = REPLACE(REPLACE(roles, \'"ROLE_GM"\', \'"ROLE_MJ"\'), \'"ROLE_PLAYER"\', \'"ROLE_JOUEUR"\')');
  }
}
