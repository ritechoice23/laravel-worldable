<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Linkers;

class LinkerResult
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public readonly string $component,
        public readonly int $linked,
        public readonly int $notFound,
        public readonly int $total,
        public readonly array $details = [],
    ) {}

    public function hasOrphans(): bool
    {
        return $this->total > 0;
    }

    public function allLinked(): bool
    {
        return $this->linked === $this->total && $this->notFound === 0;
    }

    public function getSuccessRate(): float
    {
        if ($this->total === 0) {
            return 100.0;
        }

        return round(($this->linked / $this->total) * 100, 2);
    }
}
