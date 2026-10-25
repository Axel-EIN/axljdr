<?php
namespace App\Service;

use App\Entity\FactionType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LocationGrouper
{
    private $visibility;
    private $titles;

    public function __construct(Visibility $visibility, ParameterBagInterface $parameters)
    {
        $this->visibility = $visibility;
        $this->titles = array_column($parameters->get('factions'), 'titre', 'nom');
    }

    public function byFaction(array $locations): array
    {
        $order = array_merge(
            array_map(fn (string $name) => 'clan-' . $name, array_keys($this->titles)),
            ['type-' . FactionType::MINEUR, 'type-' . FactionType::NON_ALIGNE, 'others']
        );

        $groups = [];
        foreach ($locations as $location) {
            if (!$this->visibility->isListed($location)) {
                continue;
            }

            $clan = $location->getClan();
            $major = $clan !== null && $clan->getType() === FactionType::MAJEUR;
            $ungrouped = $clan === null || ($major && !isset($this->titles[$clan->getNom()]));

            if ($ungrouped) {
                $key = 'others';
                $groups[$key]['ungrouped'] = true;
            } elseif ($clan->getType() === FactionType::ENNEMI) {
                $key = 'others';
            } else {
                $key = $major ? 'clan-' . $clan->getNom() : 'type-' . $clan->getType();
                $groups[$key]['title'] = $major ? $this->titles[$clan->getNom()] : FactionType::GROUPS[$clan->getType()];
            }

            if (!$ungrouped && $this->visibility->isListed($clan)) {
                $groups[$key]['clans'][$clan->getId()] = $clan;
            }

            $groups[$key]['locations'][] = $location;
        }

        if (isset($groups['others'])) {
            $enemies = !empty($groups['others']['clans']);
            $groups['others']['title'] = match (true) {
                $enemies && isset($groups['others']['ungrouped']) => 'Ennemis et autres',
                $enemies => FactionType::GROUPS[FactionType::ENNEMI],
                default => 'Autres',
            };
        }

        return array_values(array_filter(array_map(fn (string $key) => $groups[$key] ?? null, $order)));
    }
}
