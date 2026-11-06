<?php

namespace App\Service;

use App\Entity\Archive;
use App\Entity\Avantage;
use App\Entity\Chapitre;
use App\Entity\Clan;
use App\Entity\Ecole;
use App\Entity\Episode;
use App\Entity\Famille;
use App\Entity\FichePersonnage;
use App\Entity\Lieu;
use App\Entity\Personnage;
use App\Entity\Scene;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class DeletionGuard
{
    private const LABELS = [
        Archive::class         => ['archive', 'archives'],
        Avantage::class        => ['avantage', 'avantages'],
        Chapitre::class        => ['chapitre', 'chapitres'],
        Clan::class            => ['faction', 'factions'],
        Ecole::class           => ['école', 'écoles'],
        Episode::class         => ['épisode', 'épisodes'],
        Famille::class         => ['famille', 'familles'],
        FichePersonnage::class => ['fiche', 'fiches'],
        Lieu::class            => ['lieu', 'lieux'],
        Personnage::class      => ['personnage', 'personnages'],
        Scene::class           => ['scène', 'scènes'],
    ];

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function refusal(object $element, string $name): ?string
    {
        $links = [];

        foreach ($this->blockingFields($element) as $class => $fields) {
            $count = $this->count($class, $fields, $element);

            if ($count > 0) {
                [$singular, $plural] = self::LABELS[$class] ?? [$class, $class];
                $links[] = $count . ' ' . ($count > 1 ? $plural : $singular);
            }
        }

        if (empty($links)) {
            return null;
        }

        return 'Impossible de supprimer « ' . $name . ' ». Éléments liés : ' . implode(', ', $links) . '.';
    }

    private function blockingFields(object $element): array
    {
        $target = $this->entityManager->getClassMetadata(get_class($element));
        $fields = [];

        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $source) {
            foreach ($source->getAssociationMappings() as $field => $mapping) {
                if ($this->blocks($target, $source, $field, $mapping)) {
                    $fields[$source->getName()][] = $field;
                }
            }
        }

        return $fields;
    }

    private function blocks(ClassMetadata $target, ClassMetadata $source, string $field, array $mapping): bool
    {
        if ($source->isMappedSuperclass || !$mapping['isOwningSide'] || !($mapping['type'] & ClassMetadata::TO_ONE)) {
            return false;
        }

        if (!is_a($target->getName(), $mapping['targetEntity'], true)) {
            return false;
        }

        if (($mapping['joinColumns'][0]['onDelete'] ?? null) !== null) {
            return false;
        }

        foreach ($target->getAssociationMappings() as $inverse) {
            if (($inverse['mappedBy'] ?? null) === $field
                && $inverse['targetEntity'] === $source->getName()
                && ($inverse['isCascadeRemove'] || $inverse['orphanRemoval'])) {
                return false;
            }
        }

        return true;
    }

    private function count(string $class, array $fields, object $element): int
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('COUNT(x)')
            ->from($class, 'x');

        foreach ($fields as $field) {
            $query->orWhere('x.' . $field . ' = :element');
        }

        return (int) $query->setParameter('element', $element)->getQuery()->getSingleScalarResult();
    }
}
