<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Personnage : le booléen est_mort devient un statut ordonné — vivant, disparu, mort.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE personnage ADD status SMALLINT DEFAULT NULL');
    $this->addSql('UPDATE personnage SET status = IF(est_mort = 1, 2, 0)');
    $this->addSql('ALTER TABLE personnage MODIFY status SMALLINT NOT NULL');
    $this->addSql('ALTER TABLE personnage DROP est_mort');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE personnage ADD est_mort TINYINT(1) DEFAULT NULL');
    $this->addSql('UPDATE personnage SET est_mort = IF(status = 2, 1, 0)');
    $this->addSql('ALTER TABLE personnage MODIFY est_mort TINYINT(1) NOT NULL');
    $this->addSql('ALTER TABLE personnage DROP status');
  }
}
