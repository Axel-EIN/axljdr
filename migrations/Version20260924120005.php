<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924120005 extends AbstractMigration
{
  public function getDescription(): string
  {
    return "Qualité d'un objet — commun, rare ou mythique — mise en avant sur sa page de détail.";
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE objet ADD quality VARCHAR(20) DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE objet DROP quality');
  }
}
