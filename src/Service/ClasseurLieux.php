<?php
namespace App\Service;

use App\Entity\FactionType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ClasseurLieux
{
    private $visibility;
    private $titres;

    public function __construct(Visibility $visibility, ParameterBagInterface $parameters)
    {
        $this->visibility = $visibility;
        $this->titres = array_column($parameters->get('factions'), 'titre', 'nom');
    }

    public function parFaction(array $lieux): array
    {
        $ordre = array_merge(
            array_map(fn (string $nom) => 'clan-' . $nom, array_keys($this->titres)),
            ['type-' . FactionType::MINEUR, 'type-' . FactionType::NON_ALIGNE, 'autres']
        );

        $groupes = [];
        foreach ($lieux as $lieu) {
            if (!$this->visibility->isListed($lieu)) {
                continue;
            }

            $clan = $lieu->getClan();
            $majeur = $clan !== null && $clan->getType() === FactionType::MAJEUR;
            $sansGroupe = $clan === null || ($majeur && !isset($this->titres[$clan->getNom()]));

            if ($sansGroupe) {
                $cle = 'autres';
                $groupes[$cle]['sansGroupe'] = true;
            } elseif ($clan->getType() === FactionType::ENNEMI) {
                $cle = 'autres';
            } else {
                $cle = $majeur ? 'clan-' . $clan->getNom() : 'type-' . $clan->getType();
                $groupes[$cle]['titre'] = $majeur ? $this->titres[$clan->getNom()] : FactionType::GROUPS[$clan->getType()];
            }

            if (!$sansGroupe && $this->visibility->isListed($clan)) {
                $groupes[$cle]['clans'][$clan->getId()] = $clan;
            }

            $groupes[$cle]['lieux'][] = $lieu;
        }

        if (isset($groupes['autres'])) {
            $ennemis = !empty($groupes['autres']['clans']);
            $groupes['autres']['titre'] = match (true) {
                $ennemis && isset($groupes['autres']['sansGroupe']) => 'Ennemis et autres',
                $ennemis => FactionType::GROUPS[FactionType::ENNEMI],
                default => 'Autres',
            };
        }

        return array_values(array_filter(array_map(fn (string $cle) => $groupes[$cle] ?? null, $ordre)));
    }
}
