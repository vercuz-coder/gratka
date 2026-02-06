<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Factory\LikeFactory;
use App\Factory\PhotoFactory;
use App\Factory\UserFactory;
use App\Repository\LikeRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LikeRepositoryTest extends KernelTestCase
{
    private LikeRepository $likeRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->likeRepository = static::getContainer()->get(LikeRepository::class);
    }

    public function testFindOneByUserAndPhotoReturnsLike(): void
    {
        // Arrange
        $user = UserFactory::createOne(['username' => 'repository_test_user']);
        $photo = PhotoFactory::createOne(['user' => $user]);
        $like = LikeFactory::createOne(['user' => $user, 'photo' => $photo]);

        // Act
        $foundLike = $this->likeRepository->findOneByUserAndPhoto($user, $photo);

        // Assert
        $this->assertNotNull($foundLike);
        $this->assertSame($user->getId(), $foundLike->getUser()->getId());
        $this->assertSame($photo->getId(), $foundLike->getPhoto()->getId());
    }

    public function testFindOneByUserAndPhotoReturnsNullWhenNotFound(): void
    {
        // Arrange
        $user = UserFactory::createOne(['username' => 'repository_user_2']);
        $photo = PhotoFactory::createOne(['user' => $user]);

        // Act
        $foundLike = $this->likeRepository->findOneByUserAndPhoto($user, $photo);

        // Assert
        $this->assertNull($foundLike);
    }
}
