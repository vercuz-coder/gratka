<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Like;
use App\Entity\Photo;
use App\Entity\User;

interface LikeRepositoryInterface
{
    public function save(Like $like, bool $flush = false): void;

    public function remove(Like $like, bool $flush = false): void;

    public function findOneByUserAndPhoto(User $user, Photo $photo): ?Like;
}
