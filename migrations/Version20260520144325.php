<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520144325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insère un Objet universel "Mains Nues / Corps" (VD 0g1) disponible pour tous les personnages.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO objet (nom, type, categorie, vd, description) "
            . "VALUES ('Mains Nues / Corps', 'DIVERS', 'ARME', '0g1', 'Combat à mains nues / corps à corps sans arme. Disponible pour tous les personnages.')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM objet WHERE nom = 'Mains Nues / Corps' AND categorie = 'ARME'");
    }
}
