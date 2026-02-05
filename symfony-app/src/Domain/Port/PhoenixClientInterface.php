<?php

declare(strict_types=1);

namespace App\Domain\Port;

interface PhoenixClientInterface
{
    public function getPhotos(string $token): array;

    public function validateToken(string $token): bool;
}
