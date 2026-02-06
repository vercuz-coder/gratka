<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Like;
use App\Entity\Photo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class LikeRepository extends ServiceEntityRepository implements LikeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    public function save(Like $like, bool $flush = false): void
    {
        $this->getEntityManager()->persist($like);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Like $like, bool $flush = false): void
    {
        $this->getEntityManager()->remove($like);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByUserAndPhoto(User $user, Photo $photo): ?Like
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user')
            ->andWhere('l.photo = :photo')
            ->setParameter('user', $user)
            ->setParameter('photo', $photo)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
