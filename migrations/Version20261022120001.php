<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022120001 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'L\'image actuelle de l\'objet devient son icône ; l\'objet reçoit une nouvelle image en 16:9.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE objet CHANGE image icon VARCHAR(255) DEFAULT NULL, ADD image VARCHAR(255) DEFAULT NULL');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE objet DROP image, CHANGE icon image VARCHAR(255) DEFAULT NULL');
  }
}
