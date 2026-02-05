<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Like;
use App\Entity\Photo;
use App\Entity\User;
use App\Repository\LikeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Throwable;

class LikeService
{
    public function __construct(
        private readonly LikeRepositoryInterface $likeRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function addLike(Photo $photo, User $user): void
    {
        try {
            $existingLike = $this->likeRepository->findOneByUserAndPhoto($user, $photo);
            if ($existingLike) {
                return;
            }

            $like = new Like();
            $like->setUser($user);
            $like->setPhoto($photo);

            $this->likeRepository->save($like);
            
            $photo->setLikeCounter($photo->getLikeCounter() + 1);
            
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new Exception('Something went wrong while liking the photo');
        }
    }

    public function removeLike(Photo $photo, User $user): void
    {
        try {
            $like = $this->likeRepository->findOneByUserAndPhoto($user, $photo);
            if (!$like) {
                return;
            }

            $this->likeRepository->remove($like);

            $photo->setLikeCounter($photo->getLikeCounter() - 1);
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new Exception('Something went wrong while unliking the photo');
        }
    }
}
