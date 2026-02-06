<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\PhotoFilter;
use App\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    /**
     * @return Photo[]
     */
    public function findFiltered(PhotoFilter $filter): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'DESC');

        // Using LOWER() LIKE LOWER() for case-insensitive search.
        // PostgreSQL's ILIKE would be more efficient but Doctrine DQL doesn't support it natively.
        // To use ILIKE, you'd need to register a custom DQL function or use native SQL.

        if (null !== $filter->location && '' !== $filter->location) {
            $qb->andWhere('LOWER(p.location) LIKE LOWER(:location)')
               ->setParameter('location', '%'.$filter->location.'%');
        }

        if (null !== $filter->camera && '' !== $filter->camera) {
            $qb->andWhere('LOWER(p.camera) LIKE LOWER(:camera)')
               ->setParameter('camera', '%'.$filter->camera.'%');
        }

        if (null !== $filter->description && '' !== $filter->description) {
            $qb->andWhere('LOWER(p.description) LIKE LOWER(:description)')
               ->setParameter('description', '%'.$filter->description.'%');
        }

        if (null !== $filter->username && '' !== $filter->username) {
            $qb->andWhere('LOWER(u.username) LIKE LOWER(:username)')
               ->setParameter('username', '%'.$filter->username.'%');
        }

        if (null !== $filter->takenAtFrom) {
            $qb->andWhere('p.takenAt >= :takenAtFrom')
               ->setParameter('takenAtFrom', $filter->takenAtFrom);
        }

        if (null !== $filter->takenAtTo) {
            $qb->andWhere('p.takenAt <= :takenAtTo')
               ->setParameter('takenAtTo', $filter->takenAtTo);
        }

        return $qb->getQuery()->getResult();
    }
}
