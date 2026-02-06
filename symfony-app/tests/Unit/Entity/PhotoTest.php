<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Photo;
use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Photo entity.
 *
 * Tests cover:
 * - Getters and setters
 * - Default values initialization
 * - Fluent interface
 * - Like counter operations
 */
#[CoversClass(Photo::class)]
final class PhotoTest extends TestCase
{
    public function testDefaultLikeCounterIsZero(): void
    {
        // Act
        $photo = new Photo();

        // Assert
        $this->assertSame(0, $photo->getLikeCounter());
    }

    public function testIdIsNullBeforePersistence(): void
    {
        // Act
        $photo = new Photo();

        // Assert
        $this->assertNull($photo->getId());
    }

    public function testSetImageUrlReturnsFluentInterface(): void
    {
        // Arrange
        $photo = new Photo();

        // Act
        $result = $photo->setImageUrl('https://example.com/photo.jpg');

        // Assert
        $this->assertSame($photo, $result);
        $this->assertSame('https://example.com/photo.jpg', $photo->getImageUrl());
    }

    public function testSetLocationReturnsFluentInterface(): void
    {
        // Arrange
        $photo = new Photo();

        // Act
        $result = $photo->setLocation('Warsaw');

        // Assert
        $this->assertSame($photo, $result);
        $this->assertSame('Warsaw', $photo->getLocation());
    }

    public function testSetLocationAcceptsNull(): void
    {
        // Arrange
        $photo = new Photo();
        $photo->setLocation('Warsaw');

        // Act
        $photo->setLocation(null);

        // Assert
        $this->assertNull($photo->getLocation());
    }

    public function testSetDescriptionReturnsFluentInterface(): void
    {
        // Arrange
        $photo = new Photo();

        // Act
        $result = $photo->setDescription('Beautiful sunset over the city');

        // Assert
        $this->assertSame($photo, $result);
        $this->assertSame('Beautiful sunset over the city', $photo->getDescription());
    }

    public function testSetCameraReturnsFluentInterface(): void
    {
        // Arrange
        $photo = new Photo();

        // Act
        $result = $photo->setCamera('Canon EOS R5');

        // Assert
        $this->assertSame($photo, $result);
        $this->assertSame('Canon EOS R5', $photo->getCamera());
    }

    public function testSetTakenAtReturnsFluentInterface(): void
    {
        // Arrange
        $photo = new Photo();
        $takenAt = new DateTimeImmutable('2024-06-15 14:30:00');

        // Act
        $result = $photo->setTakenAt($takenAt);

        // Assert
        $this->assertSame($photo, $result);
        $this->assertSame($takenAt, $photo->getTakenAt());
    }

    public function testSetTakenAtAcceptsNull(): void
    {
        // Arrange
        $photo = new Photo();
        $photo->setTakenAt(new DateTimeImmutable());

        // Act
        $photo->setTakenAt(null);

        // Assert
        $this->assertNull($photo->getTakenAt());
    }

    public function testSetUserReturnsFluentInterface(): void
    {
        // Arrange
        $photo = new Photo();
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');

        // Act
        $result = $photo->setUser($user);

        // Assert
        $this->assertSame($photo, $result);
        $this->assertSame($user, $photo->getUser());
    }

    public function testSetLikeCounterReturnsFluentInterface(): void
    {
        // Arrange
        $photo = new Photo();

        // Act
        $result = $photo->setLikeCounter(5);

        // Assert
        $this->assertSame($photo, $result);
        $this->assertSame(5, $photo->getLikeCounter());
    }

    public function testLikeCounterCanBeIncremented(): void
    {
        // Arrange
        $photo = new Photo();
        $photo->setLikeCounter(10);

        // Act
        $photo->setLikeCounter($photo->getLikeCounter() + 1);

        // Assert
        $this->assertSame(11, $photo->getLikeCounter());
    }

    public function testLikeCounterCanBeDecremented(): void
    {
        // Arrange
        $photo = new Photo();
        $photo->setLikeCounter(10);

        // Act
        $photo->setLikeCounter($photo->getLikeCounter() - 1);

        // Assert
        $this->assertSame(9, $photo->getLikeCounter());
    }

    public function testFullEntityConfiguration(): void
    {
        // Arrange
        $user = new User();
        $user->setUsername('photographer');
        $user->setEmail('photo@example.com');

        $takenAt = new DateTimeImmutable('2024-06-15 14:30:00');

        // Act
        $photo = new Photo();
        $photo->setImageUrl('https://example.com/beautiful.jpg')
            ->setLocation('Kraków')
            ->setDescription('Mountain view at sunset')
            ->setCamera('Sony A7III')
            ->setTakenAt($takenAt)
            ->setUser($user)
            ->setLikeCounter(42);

        // Assert
        $this->assertSame('https://example.com/beautiful.jpg', $photo->getImageUrl());
        $this->assertSame('Kraków', $photo->getLocation());
        $this->assertSame('Mountain view at sunset', $photo->getDescription());
        $this->assertSame('Sony A7III', $photo->getCamera());
        $this->assertSame($takenAt, $photo->getTakenAt());
        $this->assertSame($user, $photo->getUser());
        $this->assertSame(42, $photo->getLikeCounter());
    }
}
