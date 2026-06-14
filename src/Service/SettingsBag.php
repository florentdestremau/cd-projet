<?php

namespace App\Service;

use App\Repository\SettingRepository;

final class SettingsBag
{
    /** @var array<string, string>|null */
    private ?array $cache = null;

    public function __construct(private readonly SettingRepository $repo)
    {
    }

    public function get(string $key, string $default = ''): string
    {
        if (null === $this->cache) {
            $this->cache = [];
            foreach ($this->repo->findAll() as $setting) {
                $this->cache[$setting->getKey()] = (string) $setting->getValue();
            }
        }

        return $this->cache[$key] ?? $default;
    }

    public function clear(): void
    {
        $this->cache = null;
    }
}
