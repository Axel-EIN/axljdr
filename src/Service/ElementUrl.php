<?php

namespace App\Service;

use App\Repository\LibraryRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ElementUrl
{
    private $router;
    private $libraryRepository;
    private $libraries;

    public function __construct(UrlGeneratorInterface $router, LibraryRepository $libraryRepository)
    {
        $this->router = $router;
        $this->libraryRepository = $libraryRepository;
    }

    public function of($element): ?string
    {
        $key = EntityRegistry::keyOf($element);

        if ($key === null) {
            return null;
        }

        $route = EntityRegistry::route($key);

        if ($route !== null) {
            return $this->router->generate($route, ['id' => $element->getId()]);
        }

        $library = $this->libraries()[$key] ?? null;

        if ($library === null) {
            return null;
        }

        $params = ['id' => $library->getId()];
        foreach (['tab' => $library->getTabField(), 'subtab' => $library->getSubTabField()] as $param => $field) {
            if (!empty($field)) {
                $params[$param] = $element->{'get' . ucfirst($field)}() ?: 'all';
            }
        }
        $params['_fragment'] = $key . $element->getId();

        return $this->router->generate('regles_library', $params);
    }

    private function libraries(): array
    {
        if ($this->libraries !== null) {
            return $this->libraries;
        }

        $this->libraries = [];
        foreach ($this->libraryRepository->findBy([], ['base' => 'DESC', 'numero' => 'ASC']) as $library) {
            $key = $library->getEntity();
            if (!empty($key) && !isset($this->libraries[$key])) {
                $this->libraries[$key] = $library;
            }
        }

        return $this->libraries;
    }
}
