<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Like;
use App\Entity\Photo;
use App\Entity\User;
use App\Repository\LikeRepositoryInterface;
use App\Service\LikeService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Unit tests for LikeService.
 *
 * Tests follow Arrange-Act-Assert pattern and cover:
 * - Adding likes (success, duplicate prevention)
 * - Removing likes (success, non-existent like)
 * - Exception handling
 */
#[CoversClass(LikeService::class)]
final class LikeServiceTest extends TestCase
{
    private LikeRepositoryInterface&MockObject $likeRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private LikeService $sut; // System Under Test

    protected function setUp(): void
    {
        $this->likeRepository = $this->createMock(LikeRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->sut = new LikeService($this->likeRepository, $this->entityManager);
    }

    // ==================== addLike() Tests ====================

    public function testAddLikeCreatesNewLikeWhenNotExists(): void
    {
        // Arrange
        $user = $this->createUser();
        $photo = $this->createPhoto(initialLikeCount: 5);

        $this->likeRepository
            ->expects($this->once())
            ->method('findOneByUserAndPhoto')
            ->with($user, $photo)
            ->willReturn(null);

        $this->likeRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(static function (Like $like) use ($user, $photo): bool {
                return $like->getUser() === $user && $like->getPhoto() === $photo;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $this->sut->addLike($photo, $user);

        // Assert
        $this->assertSame(6, $photo->getLikeCounter());
    }

    public function testAddLikeDoesNothingWhenAlreadyLiked(): void
    {
        // Arrange
        $user = $this->createUser();
        $photo = $this->createPhoto(initialLikeCount: 5);
        $existingLike = $this->createLike($user, $photo);

        $this->likeRepository
            ->expects($this->once())
            ->method('findOneByUserAndPhoto')
            ->with($user, $photo)
            ->willReturn($existingLike);

        $this->likeRepository
            ->expects($this->never())
            ->method('save');

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        // Act
        $this->sut->addLike($photo, $user);

        // Assert - like counter should remain unchanged
        $this->assertSame(5, $photo->getLikeCounter());
    }

    public function testAddLikeIncrementsLikeCounterFromZero(): void
    {
        // Arrange
        $user = $this->createUser();
        $photo = $this->createPhoto(initialLikeCount: 0);

        $this->likeRepository
            ->method('findOneByUserAndPhoto')
            ->willReturn(null);

        $this->likeRepository
            ->expects($this->once())
            ->method('save');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $this->sut->addLike($photo, $user);

        // Assert
        $this->assertSame(1, $photo->getLikeCounter());
    }

    public function testAddLikeThrowsExceptionWhenRepositoryFails(): void
    {
        // Arrange
        $user = $this->createUser();
        $photo = $this->createPhoto();

        $this->likeRepository
            ->method('findOneByUserAndPhoto')
            ->willThrowException(new RuntimeException('Database error'));

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong while liking the photo');

        // Act
        $this->sut->addLike($photo, $user);
    }

    public function testAddLikeThrowsExceptionWhenSaveFails(): void
    {
        // Arrange
        $user = $this->createUser();
        $photo = $this->createPhoto();

        $this->likeRepository
            ->method('findOneByUserAndPhoto')
            ->willReturn(null);

        $this->likeRepository
            ->method('save')
            ->willThrowException(new RuntimeException('Save failed'));

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong while liking the photo');

        // Act
        $this->sut->addLike($photo, $user);
    }

    // ==================== removeLike() Tests ====================

    public function testRemoveLikeDeletesExistingLike(): void
    {
        // Arrange
        $user = $this->createUser();
        $photo = $this->createPhoto(initialLikeCount: 5);
        $like = $this->createLike($user, $photo);

        $this->likeRepository
            ->expects($this->once())
            ->method('findOneByUserAndPhoto')
            ->with($user, $photo)
            ->willReturn($like);

        $this->likeRepository
            ->expects($this->once())
            ->method('remove')
            ->with($like);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $this->sut->removeLike($photo, $user);

        // Assert
        $this->assertSame(4, $photo->getLikeCounter());
    }

    public function testRemoveLikeDoesNothingWhenLikeDoesNotExist(): void
    {
        // Arrange
        $user = $this->createUser();
        $photo = $this->createPhoto(initialLikeCount: 5);

        $this->likeRepository
            ->expects($this->once())
            ->method('findOneByUserAndPhoto')
            ->with($user, $photo)
            ->willReturn(null);

        $this->likeRepository
            ->expects($this->never())
            ->method('remove');

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        // Act
        $this->sut->removeLike($photo, $user);

        // Assert - like counter should remain unchanged
        $this->assertSame(5, $photo->getLikeCounter());
    }

    public function testRemoveLikeDecrementsCounterToZero(): void
    {
        // Arrange
        $user = $this->createUser();
        $photo = $this->createPhoto(initialLikeCount: 1);
        $like = $this->createLike($user, $photo);

        $this->likeRepository
            ->method('findOneByUserAndPhoto')
            ->willReturn($like);

        $this->likeRepository
            ->expects($this->once())
            ->method('remove')
            ->with($like);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $this->sut->removeLike($photo, $user);

        // Assert
        $this->assertSame(0, $photo->getLikeCounter());
    }

    public function testRemoveLikeThrowsExceptionWhenRepositoryFails(): void
    {
        // Arrange
        $user = $this->createUser();
        $photo = $this->createPhoto();

        $this->likeRepository
            ->method('findOneByUserAndPhoto')
            ->willThrowException(new RuntimeException('Database error'));

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong while unliking the photo');

        // Act
        $this->sut->removeLike($photo, $user);
    }

    public function testRemoveLikeThrowsExceptionWhenRemoveFails(): void
    {
        // Arrange
        $user = $this->createUser();
        $photo = $this->createPhoto();
        $like = $this->createLike($user, $photo);

        $this->likeRepository
            ->method('findOneByUserAndPhoto')
            ->willReturn($like);

        $this->likeRepository
            ->method('remove')
            ->willThrowException(new RuntimeException('Remove failed'));

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong while unliking the photo');

        // Act
        $this->sut->removeLike($photo, $user);
    }

    // ==================== Helper Methods ====================

    private function createUser(): User
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');

        return $user;
    }

    private function createPhoto(int $initialLikeCount = 0): Photo
    {
        $photo = new Photo();
        $photo->setImageUrl('https://example.com/photo.jpg');
        $photo->setLikeCounter($initialLikeCount);

        return $photo;
    }

    private function createLike(User $user, Photo $photo): Like
    {
        $like = new Like();
        $like->setUser($user);
        $like->setPhoto($photo);

        return $like;
    }
}
