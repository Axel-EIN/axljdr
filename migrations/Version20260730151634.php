<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Aligns the chapitre table with the Chapitre entity mapping: citation and
 * image are nullable=true on the entity but were left NOT NULL in the
 * original CREATE TABLE, causing a 500 error when creating a chapitre
 * without an image (the common case, since both fields are optional in
 * AdminChapitreType).
 */
final class Version20260730151634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make chapitre.citation and chapitre.image nullable to match the Chapitre entity mapping';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chapitre CHANGE citation citation VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chapitre CHANGE citation citation VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL');
    }
}
