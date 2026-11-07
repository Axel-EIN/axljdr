<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;

class Throttle
{
    private const PERIOD = 3600;

    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function allow(string $action, Request $request, int $maxPerHour): bool
    {
        $item = $this->cache->getItem($action . '_' . hash('sha256', (string) $request->getClientIp()));
        $doneAt = array_filter($item->isHit() ? $item->get() : [], fn($time) => $time > time() - self::PERIOD);

        if (count($doneAt) >= $maxPerHour) {
            return false;
        }

        $doneAt[] = time();
        $this->cache->save($item->set($doneAt)->expiresAfter(self::PERIOD));

        return true;
    }
}
