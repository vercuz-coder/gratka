<?php

declare(strict_types=1);

namespace App\DTO;

final class PhotoFilter
{
    public function __construct(
        public ?string $location = null,
        public ?string $camera = null,
        public ?string $description = null,
        public ?\DateTimeImmutable $takenAtFrom = null,
        public ?\DateTimeImmutable $takenAtTo = null,
        public ?string $username = null,
    ) {}

    public function isEmpty(): bool
    {
        return $this->location === null
            && $this->camera === null
            && $this->description === null
            && $this->takenAtFrom === null
            && $this->takenAtTo === null
            && $this->username === null;
    }
}
