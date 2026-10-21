<?php

namespace App\Command;

use App\Entity\Episode;
use App\Entity\Personnage;
use App\Service\ImageNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class OptimizeImagesCommand extends Command
{
    protected static $defaultName = 'app:images:optimize';

    private const TYPES = [
        'illustration' => ['entity' => Personnage::class, 'field' => 'illustration', 'preset' => 'vertical450'],
        'episode'      => ['entity' => Episode::class,    'field' => 'image',        'preset' => 'landscape720'],
    ];

    private $entityManager;
    private $imageNormalizer;
    private $propertyAccessor;
    private $publicDirectory;

    public function __construct(EntityManagerInterface $entityManager, ImageNormalizer $imageNormalizer, PropertyAccessorInterface $propertyAccessor, string $publicDirectory)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->imageNormalizer = $imageNormalizer;
        $this->propertyAccessor = $propertyAccessor;
        $this->publicDirectory = $publicDirectory;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Ramène les images d\'un type à son preset (taille et format), met à jour leur chemin en base, puis crée leur version réduite.')
            ->addArgument('types', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Types d\'image à traiter : ' . implode(', ', array_keys(self::TYPES)));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $types = $input->getArgument('types');

        $inconnus = array_diff($types, array_keys(self::TYPES));
        if ($inconnus !== []) {
            $io->error('Type(s) inconnu(s) : ' . implode(', ', $inconnus) . '. Types possibles : ' . implode(', ', array_keys(self::TYPES)) . '.');

            return Command::INVALID;
        }

        $echecs = [];

        foreach ($types as $type) {
            $echecs = array_merge($echecs, $this->optimiser($type, $io));
        }

        $this->entityManager->flush();

        foreach ($echecs as $echec) {
            $io->warning($echec);
        }

        return $echecs === [] ? Command::SUCCESS : Command::FAILURE;
    }

    private function optimiser(string $type, SymfonyStyle $io): array
    {
        ['entity' => $entity, 'field' => $field, 'preset' => $preset] = self::TYPES[$type];
        $normalises = $reduits = 0;
        $echecs = [];

        $elements = $this->entityManager->getRepository($entity)->createQueryBuilder('e')
            ->andWhere('e.' . $field . ' IS NOT NULL')
            ->getQuery()
            ->getResult();

        foreach ($elements as $element) {
            $image = $this->propertyAccessor->getValue($element, $field);
            $chemin = $this->publicDirectory . '/' . ltrim($image, '/');

            if (!is_file($chemin)) {
                $echecs[] = $image . ' : fichier introuvable';
                continue;
            }

            if ($this->imageNormalizer->matchesPreset($chemin, $preset)) {
                if (!$this->imageNormalizer->reduce($chemin, $preset)) {
                    $echecs[] = $image . ' : version réduite impossible';
                    continue;
                }
            } else {
                $normalise = $this->imageNormalizer->normalize($chemin, $preset);

                if ($normalise === null) {
                    $echecs[] = $image . ' : normalisation impossible';
                    continue;
                }

                $this->propertyAccessor->setValue($element, $field, dirname($image) . '/' . basename($normalise));
                $normalises++;
            }

            $reduits++;
        }

        $io->writeln(sprintf('%-12s %3d image(s) normalisée(s), %3d version(s) réduite(s) créée(s)', $type, $normalises, $reduits));

        return $echecs;
    }
}
