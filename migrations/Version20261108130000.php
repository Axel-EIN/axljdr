<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108130000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'lieu : ajout de gallery, liste ordonnée des images du carrousel.';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu ADD gallery JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE lieu DROP gallery');
  }
}
