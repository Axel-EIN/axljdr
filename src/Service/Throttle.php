<?php

namespace App\Service;

use Psr\Cache\CacheItemInterface;
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
        if ($this->exceeded($action, $request, $maxPerHour)) {
            return false;
        }

        $this->hit($action, $request);

        return true;
    }

    public function exceeded(string $action, Request $request, int $maxPerHour): bool
    {
        return count($this->recent($this->item($action, $request))) >= $maxPerHour;
    }

    public function hit(string $action, Request $request): void
    {
        $item = $this->item($action, $request);
        $doneAt = $this->recent($item);
        $doneAt[] = time();

        $this->cache->save($item->set($doneAt)->expiresAfter(self::PERIOD));
    }

    private function item(string $action, Request $request): CacheItemInterface
    {
        return $this->cache->getItem($action . '_' . hash('sha256', (string) $request->getClientIp()));
    }

    private function recent(CacheItemInterface $item): array
    {
        return array_filter($item->isHit() ? $item->get() : [], fn($time) => $time > time() - self::PERIOD);
    }
}
