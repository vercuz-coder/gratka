<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Like;
use App\Entity\Photo;
use App\Entity\User;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Like entity.
 *
 * Tests cover:
 * - Getters and setters
 * - Default values initialization
 * - Fluent interface
 */
#[CoversClass(Like::class)]
final class LikeTest extends TestCase
{
    public function testConstructorInitializesCreatedAt(): void
    {
        // Act
        $like = new Like();

        // Assert
        $this->assertInstanceOf(DateTimeInterface::class, $like->getCreatedAt());
    }

    public function testSetUserReturnsFluentInterface(): void
    {
        // Arrange
        $like = new Like();
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');

        // Act
        $result = $like->setUser($user);

        // Assert
        $this->assertSame($like, $result);
        $this->assertSame($user, $like->getUser());
    }

    public function testSetPhotoReturnsFluentInterface(): void
    {
        // Arrange
        $like = new Like();
        $photo = new Photo();
        $photo->setImageUrl('https://example.com/photo.jpg');

        // Act
        $result = $like->setPhoto($photo);

        // Assert
        $this->assertSame($like, $result);
        $this->assertSame($photo, $like->getPhoto());
    }

    public function testSetCreatedAtReturnsFluentInterface(): void
    {
        // Arrange
        $like = new Like();
        $createdAt = new DateTime('2024-01-15 10:30:00');

        // Act
        $result = $like->setCreatedAt($createdAt);

        // Assert
        $this->assertSame($like, $result);
        $this->assertSame($createdAt, $like->getCreatedAt());
    }

    public function testFullEntityConfiguration(): void
    {
        // Arrange
        $user = new User();
        $user->setUsername('photographer');
        $user->setEmail('photo@example.com');

        $photo = new Photo();
        $photo->setImageUrl('https://example.com/beautiful.jpg');

        $createdAt = new DateTime('2024-06-15 14:30:00');

        // Act
        $like = new Like();
        $like->setUser($user)
            ->setPhoto($photo)
            ->setCreatedAt($createdAt);

        // Assert
        $this->assertSame($user, $like->getUser());
        $this->assertSame($photo, $like->getPhoto());
        $this->assertSame($createdAt, $like->getCreatedAt());
    }
}
