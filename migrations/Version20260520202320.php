<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520202320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le flag xp_bonus à participation (boost x2 pour les retardataires).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE participation ADD xp_bonus TINYINT(1) NOT NULL DEFAULT 0");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE participation DROP xp_bonus');
    }
}
