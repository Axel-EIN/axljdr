<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260906120001 extends AbstractMigration
{
  private const TABLES = [
    'personnage', 'lieu', 'clan', 'archive', 'lore',
    'objet', 'ecole', 'rule', 'library', 'sort', 'competence', 'avantage',
  ];

  public function getDescription(): string
  {
    return 'Nouveaux paliers access (VALUABLE/COMMON croisés avec HIDDEN/TEASED, puis PUBLISHED_PLAYER et PUBLISHED_GUEST). Tout repasse en COMMON_HIDDEN avec sa date de publication remise à zéro, les épisodes gardent leur visibilité tout public, et la table des déblocages est vidée : le MJ reclasse ensuite à la main.';
  }

  public function up(Schema $schema): void
  {
    foreach (self::TABLES as $table) {
      $this->addSql(sprintf('UPDATE %s SET access = 2, published_at = NULL', $table));
    }

    $this->addSql('UPDATE episode SET access = 5');
    $this->addSql('DELETE FROM character_unlock');
  }

  public function down(Schema $schema): void
  {
    $this->throwIrreversibleMigrationException(
      'Les paliers et les déblocages précédents sont supprimés, pas déplaçables : restaurer une sauvegarde.'
    );
  }
}
