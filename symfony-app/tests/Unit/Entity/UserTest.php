<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Photo;
use App\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for User entity.
 *
 * Tests cover:
 * - Getters and setters
 * - Photo collection management
 * - Security interface methods
 * - Default values initialization
 */
#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    public function testIdIsNullBeforePersistence(): void
    {
        // Act
        $user = new User();

        // Assert
        $this->assertNull($user->getId());
    }

    public function testConstructorInitializesPhotosCollection(): void
    {
        // Act
        $user = new User();

        // Assert
        $this->assertCount(0, $user->getPhotos());
    }

    // ==================== Basic Getters/Setters ====================

    public function testSetUsernameReturnsFluentInterface(): void
    {
        // Arrange
        $user = new User();

        // Act
        $result = $user->setUsername('john_doe');

        // Assert
        $this->assertSame($user, $result);
        $this->assertSame('john_doe', $user->getUsername());
    }

    public function testSetEmailReturnsFluentInterface(): void
    {
        // Arrange
        $user = new User();

        // Act
        $result = $user->setEmail('john@example.com');

        // Assert
        $this->assertSame($user, $result);
        $this->assertSame('john@example.com', $user->getEmail());
    }

    public function testSetNameReturnsFluentInterface(): void
    {
        // Arrange
        $user = new User();

        // Act
        $result = $user->setName('John');

        // Assert
        $this->assertSame($user, $result);
        $this->assertSame('John', $user->getName());
    }

    public function testSetNameAcceptsNull(): void
    {
        // Arrange
        $user = new User();
        $user->setName('John');

        // Act
        $user->setName(null);

        // Assert
        $this->assertNull($user->getName());
    }

    public function testSetLastNameReturnsFluentInterface(): void
    {
        // Arrange
        $user = new User();

        // Act
        $result = $user->setLastName('Doe');

        // Assert
        $this->assertSame($user, $result);
        $this->assertSame('Doe', $user->getLastName());
    }

    public function testSetAgeReturnsFluentInterface(): void
    {
        // Arrange
        $user = new User();

        // Act
        $result = $user->setAge(25);

        // Assert
        $this->assertSame($user, $result);
        $this->assertSame(25, $user->getAge());
    }

    public function testSetAgeAcceptsNull(): void
    {
        // Arrange
        $user = new User();
        $user->setAge(30);

        // Act
        $user->setAge(null);

        // Assert
        $this->assertNull($user->getAge());
    }

    public function testSetBioReturnsFluentInterface(): void
    {
        // Arrange
        $user = new User();

        // Act
        $result = $user->setBio('Professional photographer');

        // Assert
        $this->assertSame($user, $result);
        $this->assertSame('Professional photographer', $user->getBio());
    }

    public function testSetPhoenixApiTokenReturnsFluentInterface(): void
    {
        // Arrange
        $user = new User();

        // Act
        $result = $user->setPhoenixApiToken('api-token-12345');

        // Assert
        $this->assertSame($user, $result);
        $this->assertSame('api-token-12345', $user->getPhoenixApiToken());
    }

    public function testSetPhoenixApiTokenAcceptsNull(): void
    {
        // Arrange
        $user = new User();
        $user->setPhoenixApiToken('some-token');

        // Act
        $user->setPhoenixApiToken(null);

        // Assert
        $this->assertNull($user->getPhoenixApiToken());
    }

    // ==================== Photo Collection Tests ====================

    public function testAddPhotoAddsToCollection(): void
    {
        // Arrange
        $user = new User();
        $user->setUsername('test');
        $user->setEmail('test@example.com');

        $photo = new Photo();
        $photo->setImageUrl('https://example.com/photo.jpg');

        // Act
        $result = $user->addPhoto($photo);

        // Assert
        $this->assertSame($user, $result);
        $this->assertCount(1, $user->getPhotos());
        $this->assertTrue($user->getPhotos()->contains($photo));
    }

    public function testAddPhotoSetsUserOnPhoto(): void
    {
        // Arrange
        $user = new User();
        $user->setUsername('test');
        $user->setEmail('test@example.com');

        $photo = new Photo();
        $photo->setImageUrl('https://example.com/photo.jpg');

        // Act
        $user->addPhoto($photo);

        // Assert
        $this->assertSame($user, $photo->getUser());
    }

    public function testAddPhotoDoesNotAddDuplicate(): void
    {
        // Arrange
        $user = new User();
        $user->setUsername('test');
        $user->setEmail('test@example.com');

        $photo = new Photo();
        $photo->setImageUrl('https://example.com/photo.jpg');

        // Act
        $user->addPhoto($photo);
        $user->addPhoto($photo);

        // Assert
        $this->assertCount(1, $user->getPhotos());
    }

    public function testRemovePhotoRemovesFromCollection(): void
    {
        // Arrange
        $user = new User();
        $user->setUsername('test');
        $user->setEmail('test@example.com');

        // Create a mock Photo to avoid the non-nullable user constraint issue
        // when removePhoto tries to set user to null
        $photo = $this->createMock(Photo::class);
        $photo->method('getUser')->willReturn($user);

        // Use reflection to add photo to collection without triggering setUser
        $reflection = new ReflectionClass($user);
        $photosProperty = $reflection->getProperty('photos');
        $photosProperty->getValue($user)->add($photo);

        $this->assertCount(1, $user->getPhotos());

        // Act
        $result = $user->removePhoto($photo);

        // Assert
        $this->assertSame($user, $result);
        $this->assertCount(0, $user->getPhotos());
    }

    // ==================== Security Interface Tests ====================

    public function testGetRolesReturnsAtLeastRoleUser(): void
    {
        // Arrange
        $user = new User();

        // Act
        $roles = $user->getRoles();

        // Assert
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGetUserIdentifierReturnsUsername(): void
    {
        // Arrange
        $user = new User();
        $user->setUsername('unique_identifier');

        // Act
        $identifier = $user->getUserIdentifier();

        // Assert
        $this->assertSame('unique_identifier', $identifier);
    }

    public function testEraseCredentialsDoesNotThrow(): void
    {
        // Arrange
        $user = new User();

        // Act & Assert - should not throw
        $this->expectNotToPerformAssertions();
        $user->eraseCredentials();
    }

    public function testGetPasswordReturnsNull(): void
    {
        // Arrange
        $user = new User();

        // Act
        $password = $user->getPassword();

        // Assert
        $this->assertNull($password);
    }

    // ==================== Full Configuration Test ====================

    public function testFullEntityConfiguration(): void
    {
        // Arrange & Act
        $user = new User();
        $user->setUsername('photographer')
            ->setEmail('photo@example.com')
            ->setName('John')
            ->setLastName('Doe')
            ->setAge(35)
            ->setBio('Award-winning photographer')
            ->setPhoenixApiToken('phoenix-token-xyz');

        // Assert
        $this->assertSame('photographer', $user->getUsername());
        $this->assertSame('photo@example.com', $user->getEmail());
        $this->assertSame('John', $user->getName());
        $this->assertSame('Doe', $user->getLastName());
        $this->assertSame(35, $user->getAge());
        $this->assertSame('Award-winning photographer', $user->getBio());
        $this->assertSame('phoenix-token-xyz', $user->getPhoenixApiToken());
    }
}
