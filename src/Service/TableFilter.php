<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class TableFilter
{
    private $accessor;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->em = $em;
    }

    public function build(iterable $elements, array $definitions): array
    {
        $filters = [];

        foreach ($definitions as $definition) {
            $parts = explode(':', $definition);
            $choices = isset($parts[2]) ? explode('|', $parts[2]) : null;
            $values = [];

            foreach ($elements as $element) {
                $value = $this->valueOf($element, $parts[0]);
                $values[] = (string) ($choices ? $choices[$value ? 0 : 1] : $value);
            }

            $options = array_values(array_unique(array_filter($values, 'strlen')));
            sort($options, SORT_NATURAL | SORT_FLAG_CASE);

            $filters[] = [
                'label' => $parts[1] ?? $parts[0],
                'options' => $options,
                'values' => $values,
            ];
        }

        return $filters;
    }

    public function prefill(object $element, array $definitions, array $selection): void
    {
        foreach ($definitions as $index => $definition) {
            $value = $selection[$index] ?? '';
            $parts = explode(':', $definition);
            $path = explode('.', $parts[0]);

            if ($value === '' || count($path) > 2 || (count($path) === 2 && isset($parts[2]))) {
                continue;
            }

            if (isset($parts[2])) {
                $value = $value === explode('|', $parts[2])[0];
            } elseif (count($path) === 2) {
                $target = $this->em->getClassMetadata(get_class($element))->getAssociationTargetClass($path[0]);
                $value = $this->em->getRepository($target)->findOneBy([$path[1] => $value]);
            }

            $this->accessor->setValue($element, $path[0], $value);
        }
    }

    public function valueOf($element, string $path)
    {
        foreach (explode('.', $path) as $property) {
            if ($element === null) {
                return null;
            }
            $element = $this->accessor->getValue($element, $property);
        }

        return $element;
    }
}
