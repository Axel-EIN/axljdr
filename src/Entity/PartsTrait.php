<?php

namespace App\Entity;

trait PartsTrait
{
    public function hasParts(): bool
    {
        for ($i = 1; $i <= 5; $i++) {
            $partie = 'part' . $i;

            if (property_exists($this, $partie) && !empty($this->$partie)) {
                return true;
            }
        }

        return false;
    }
}
