<?php

namespace App\Command;

use App\Entity\Personnage;
use App\Service\ImageNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class OptimizeIllustrationsCommand extends Command
{
    protected static $defaultName = 'app:illustrations:optimize';

    private const PRESET = 'vertical450';

    private $entityManager;
    private $imageNormalizer;
    private $publicDirectory;

    public function __construct(EntityManagerInterface $entityManager, ImageNormalizer $imageNormalizer, string $publicDirectory)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->imageNormalizer = $imageNormalizer;
        $this->publicDirectory = $publicDirectory;
    }

    protected function configure(): void
    {
        $this->setDescription('Convertit en JPEG les illustrations de personnage dans un autre format, met à jour leur chemin en base, puis crée leur version réduite.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $convertis = $reduits = 0;
        $echecs = [];

        $personnages = $this->entityManager->getRepository(Personnage::class)->createQueryBuilder('p')
            ->andWhere('p.illustration IS NOT NULL')
            ->getQuery()
            ->getResult();

        foreach ($personnages as $personnage) {
            $illustration = $personnage->getIllustration();
            $chemin = $this->publicDirectory . '/' . ltrim($illustration, '/');

            if (!is_file($chemin)) {
                $echecs[] = $illustration . ' : fichier introuvable';
                continue;
            }

            if ((@getimagesize($chemin)[2] ?? null) !== IMAGETYPE_JPEG) {
                $converti = $this->imageNormalizer->convert($chemin, IMAGETYPE_JPEG);

                if ($converti === null) {
                    $echecs[] = $illustration . ' : conversion impossible';
                    continue;
                }

                $chemin = $converti;
                $personnage->setIllustration(dirname($illustration) . '/' . basename($converti));
                $convertis++;
            }

            if (!$this->imageNormalizer->reduce($chemin, self::PRESET)) {
                $echecs[] = $illustration . ' : version réduite impossible';
                continue;
            }

            $reduits++;
        }

        $this->entityManager->flush();

        foreach ($echecs as $echec) {
            $io->warning($echec);
        }

        $io->success(sprintf('%d illustration(s) convertie(s) en JPEG, %d version(s) réduite(s) créée(s).', $convertis, $reduits));

        return $echecs === [] ? Command::SUCCESS : Command::FAILURE;
    }
}
