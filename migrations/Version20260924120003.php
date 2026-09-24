<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924120003 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Signalement physique du personnage — âge, taille, poids, cheveux, yeux — et texte d'apparence, pour les deux premiers blocs du verso de la fiche.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fiche_personnage ADD age INT DEFAULT NULL, ADD height VARCHAR(20) DEFAULT NULL, ADD weight VARCHAR(20) DEFAULT NULL, ADD hair VARCHAR(40) DEFAULT NULL, ADD eyes VARCHAR(40) DEFAULT NULL, ADD appearance LONGTEXT DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE fiche_personnage DROP age, DROP height, DROP weight, DROP hair, DROP eyes, DROP appearance');
  }
}
