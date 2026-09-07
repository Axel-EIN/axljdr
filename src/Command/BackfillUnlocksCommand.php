<?php

namespace App\Command;

use App\Entity\Access;
use App\Entity\Unlock;
use App\Service\EntityRegistry;
use App\Service\Unlocker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class BackfillUnlocksCommand extends Command
{
    protected static $defaultName = 'app:unlocks:backfill';

    private const CIBLES = [
        'personnage' => 'e.estPj = false',
        'lieu' => null,
    ];

    private $entityManager;
    private $unlocker;

    public function __construct(EntityManagerInterface $entityManager, Unlocker $unlocker)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->unlocker = $unlocker;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Peuple les déblocages des éléments en palier automatique à partir des rencontres et visites déjà jouées.')
            ->addOption(
                'date',
                null,
                InputOption::VALUE_REQUIRED,
                'Date portée par les déblocages créés. Une date passée évite que tout le catalogue se marque « Nouveau » d\'un coup.',
                '-1 month'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $quand = new \DateTime($input->getOption('date'));
        $avant = $this->compterDeblocages();

        foreach (self::CIBLES as $cle => $condition) {
            $qb = $this->entityManager->createQueryBuilder()
                ->select('e')
                ->from(EntityRegistry::className($cle), 'e')
                ->andWhere('e.access = :palier')
                ->setParameter('palier', Access::AUTO);

            if ($condition !== null) {
                $qb->andWhere($condition);
            }

            $elements = $qb->getQuery()->getResult();

            foreach ($elements as $element) {
                $this->unlocker->syncAccess($element, $quand);
            }

            $io->writeln(sprintf('%-12s %4d élément(s) en palier automatique', $cle, count($elements)));
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            '%d déblocage(s) ajouté(s), datés du %s.',
            $this->compterDeblocages() - $avant,
            $quand->format('d/m/Y')
        ));

        return Command::SUCCESS;
    }

    private function compterDeblocages(): int
    {
        return (int) $this->entityManager
            ->createQuery('SELECT COUNT(u.id) FROM ' . Unlock::class . ' u')
            ->getSingleScalarResult();
    }
}
