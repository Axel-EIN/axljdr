<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260730193356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rend classe.icone, description et citation nullables : NOT NULL en base alors que le mapping autorise le vide.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE classe CHANGE icone icone VARCHAR(255) DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE citation citation VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE classe CHANGE icone icone VARCHAR(255) NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE citation citation VARCHAR(255) NOT NULL');
    }
}
