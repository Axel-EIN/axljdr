<?php

namespace App\Command;

use App\Entity\Archive;
use App\Entity\Chapitre;
use App\Entity\Clan;
use App\Entity\Classe;
use App\Entity\Ecole;
use App\Entity\Episode;
use App\Entity\Famille;
use App\Entity\Library;
use App\Entity\Lieu;
use App\Entity\Lore;
use App\Entity\Objet;
use App\Entity\Personnage;
use App\Entity\Rule;
use App\Entity\Saison;
use App\Entity\Scene;
use App\Entity\Utilisateur;
use App\Service\NameTagger;
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

    public const TYPES = [
        'illustration' => ['entity' => Personnage::class, 'field' => 'illustration', 'preset' => 'vertical450'],
        'saison'       => ['entity' => Saison::class,     'field' => 'image',        'preset' => 'landscape1080'],
        'chapitre'     => ['entity' => Chapitre::class,   'field' => 'image',        'preset' => 'landscape1080'],
        'episode'      => ['entity' => Episode::class,    'field' => 'image',        'preset' => 'landscape720'],
        'scene'        => ['entity' => Scene::class,      'field' => 'image',        'preset' => 'landscape720'],
        'archive'      => ['entity' => Archive::class,    'field' => 'image',        'preset' => 'landscape720'],
        'clan'         => ['entity' => Clan::class,       'field' => 'image',        'preset' => 'landscape720'],
        'lieu'         => ['entity' => Lieu::class,       'field' => 'image',        'preset' => 'landscape720'],
        'ecole'        => ['entity' => Ecole::class,      'field' => 'image',        'preset' => 'fourthird900'],
        'rule'         => ['entity' => Rule::class,       'field' => 'image',        'preset' => 'fourthird900'],
        'lore'         => ['entity' => Lore::class,       'field' => 'image',        'preset' => 'fourthird900'],
        'library'      => ['entity' => Library::class,    'field' => 'image',        'preset' => 'fourthird900'],
        'clan-mon'     => ['entity' => Clan::class,       'field' => 'mon',          'preset' => 'square320'],
        'famille-mon'  => ['entity' => Famille::class,    'field' => 'mon',          'preset' => 'square320'],
        'classe-icone' => ['entity' => Classe::class,     'field' => 'icone',        'preset' => 'square320'],
        'lieu-icone'   => ['entity' => Lieu::class,       'field' => 'icone',        'preset' => 'square192'],
        'plan'         => ['entity' => Lieu::class,       'field' => 'planMap',      'preset' => 'map1920'],
        'objet'        => ['entity' => Objet::class,      'field' => 'image',        'preset' => 'landscape720'],
        'objet-icon'   => ['entity' => Objet::class,      'field' => 'icon',         'preset' => 'square320'],
        'portrait'     => ['entity' => Personnage::class, 'field' => 'icone',        'preset' => 'square240'],
        'classe'       => ['entity' => Classe::class,     'field' => 'image',        'preset' => 'landscape720'],
        'avatar'       => ['entity' => Utilisateur::class, 'field' => 'avatar',      'preset' => 'square320'],
    ];

    private $entityManager;
    private $imageNormalizer;
    private $propertyAccessor;
    private $nameTagger;
    private $publicDirectory;
    private $renommages = [];
    private $absents = [];

    public function __construct(EntityManagerInterface $entityManager, ImageNormalizer $imageNormalizer, PropertyAccessorInterface $propertyAccessor, NameTagger $nameTagger, string $publicDirectory)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->imageNormalizer = $imageNormalizer;
        $this->propertyAccessor = $propertyAccessor;
        $this->nameTagger = $nameTagger;
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

        if ($this->renommages !== []) {
            $io->writeln(sprintf('%d texte(s) balisé(s) mis à jour pour %d image(s) renommée(s)', $this->remplacerDansLesTextes(), count($this->renommages)));
        }

        foreach ($this->absents as $absent) {
            $io->note($absent . ' : fichier introuvable, ignoré');
        }

        foreach ($echecs as $echec) {
            $io->warning($echec);
        }

        return $echecs === [] ? Command::SUCCESS : Command::FAILURE;
    }

    private function optimiser(string $type, SymfonyStyle $io): array
    {
        ['entity' => $entity, 'field' => $field, 'preset' => $preset] = self::TYPES[$type];
        $avecReduite = isset($this->imageNormalizer->getPreset($preset)['reduced']);
        $normalises = $reduits = 0;
        $echecs = [];

        $elements = $this->entityManager->getRepository($entity)->createQueryBuilder('e')
            ->andWhere('e.' . $field . ' IS NOT NULL')
            ->andWhere('e.' . $field . " <> ''")
            ->getQuery()
            ->getResult();

        foreach ($elements as $element) {
            $image = $this->propertyAccessor->getValue($element, $field);
            $chemin = $this->publicDirectory . '/' . ltrim($image, '/');

            if (!is_file($chemin)) {
                $this->absents[] = $image;
                continue;
            }

            if (!$this->imageNormalizer->matchesPreset($chemin, $preset)) {
                $normalise = $this->imageNormalizer->normalize($chemin, $preset);

                if ($normalise === null) {
                    $echecs[] = $image . ' : normalisation impossible';
                    continue;
                }

                $nouvelleImage = dirname($image) . '/' . basename($normalise);
                $this->propertyAccessor->setValue($element, $field, $nouvelleImage);
                $normalises++;

                if ($nouvelleImage !== $image) {
                    $this->renommages[$image] = $nouvelleImage;
                }
            } elseif ($avecReduite && !$this->imageNormalizer->reduce($chemin, $preset)) {
                $echecs[] = $image . ' : version réduite impossible';
                continue;
            }

            if ($avecReduite) {
                $reduits++;
            }
        }

        $io->writeln(sprintf('%-12s %3d image(s) normalisée(s), %3d version(s) réduite(s) à jour', $type, $normalises, $reduits));

        return $echecs;
    }

    private function remplacerDansLesTextes(): int
    {
        $lignes = 0;
        foreach ($this->renommages as $ancien => $nouveau) {
            $lignes += $this->nameTagger->replacePath($ancien, $nouveau);
        }

        return $lignes;
    }
}
