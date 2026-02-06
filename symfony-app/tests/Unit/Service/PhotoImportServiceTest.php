<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Domain\Port\PhoenixClientInterface;
use App\Entity\Photo;
use App\Entity\User;
use App\Service\PhotoImportService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Unit tests for PhotoImportService.
 *
 * Tests follow Arrange-Act-Assert pattern and cover:
 * - Token validation (missing, invalid)
 * - Photo import (success, empty response, duplicates)
 * - Data mapping (location, description, camera, taken_at)
 */
#[CoversClass(PhotoImportService::class)]
final class PhotoImportServiceTest extends TestCase
{
    private PhoenixClientInterface&MockObject $phoenixClient;
    private EntityManagerInterface&MockObject $entityManager;
    private PhotoImportService $sut; // System Under Test

    protected function setUp(): void
    {
        $this->phoenixClient = $this->createMock(PhoenixClientInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->sut = new PhotoImportService($this->phoenixClient, $this->entityManager);
    }

    // ==================== Token Validation Tests ====================

    public function testImportPhotosThrowsExceptionWhenTokenIsNull(): void
    {
        // Arrange
        $user = $this->createUserWithToken(null);

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Please set your Phoenix API token first.');

        // Act
        $this->sut->importPhotosForUser($user);
    }

    public function testImportPhotosThrowsExceptionWhenTokenIsEmpty(): void
    {
        // Arrange
        $user = $this->createUserWithToken('');

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Please set your Phoenix API token first.');

        // Act
        $this->sut->importPhotosForUser($user);
    }

    public function testImportPhotosThrowsExceptionWhenTokenIsInvalid(): void
    {
        // Arrange
        $user = $this->createUserWithToken('invalid-token');

        $this->phoenixClient
            ->expects($this->once())
            ->method('validateToken')
            ->with('invalid-token')
            ->willReturn(false);

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Wrong access token. Please check your token and try again.');

        // Act
        $this->sut->importPhotosForUser($user);
    }

    // ==================== Successful Import Tests ====================

    public function testImportPhotosReturnsZeroWhenApiReturnsEmptyArray(): void
    {
        // Arrange
        $user = $this->createUserWithToken('valid-token');

        $this->phoenixClient
            ->method('validateToken')
            ->with('valid-token')
            ->willReturn(true);

        $this->phoenixClient
            ->method('getPhotos')
            ->with('valid-token')
            ->willReturn([]);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        // Act
        $result = $this->sut->importPhotosForUser($user);

        // Assert
        $this->assertSame(0, $result);
    }

    public function testImportPhotosImportsSinglePhoto(): void
    {
        // Arrange
        $user = $this->createUserWithToken('valid-token');
        $photoData = [
            [
                'photo_url' => 'https://example.com/photo1.jpg',
                'location' => 'Warsaw',
                'description' => 'Beautiful sunset',
                'camera' => 'Canon EOS R5',
                'taken_at' => '2024-01-15T10:30:00Z',
            ],
        ];

        $this->phoenixClient
            ->method('validateToken')
            ->willReturn(true);

        $this->phoenixClient
            ->method('getPhotos')
            ->willReturn($photoData);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (Photo $photo) use ($user): bool {
                return 'https://example.com/photo1.jpg' === $photo->getImageUrl()
                    && 'Warsaw' === $photo->getLocation()
                    && 'Beautiful sunset' === $photo->getDescription()
                    && 'Canon EOS R5' === $photo->getCamera()
                    && null !== $photo->getTakenAt()
                    && $photo->getUser() === $user;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->sut->importPhotosForUser($user);

        // Assert
        $this->assertSame(1, $result);
    }

    public function testImportPhotosImportsMultiplePhotos(): void
    {
        // Arrange
        $user = $this->createUserWithToken('valid-token');
        $photoData = [
            ['photo_url' => 'https://example.com/photo1.jpg'],
            ['photo_url' => 'https://example.com/photo2.jpg'],
            ['photo_url' => 'https://example.com/photo3.jpg'],
        ];

        $this->phoenixClient
            ->method('validateToken')
            ->willReturn(true);

        $this->phoenixClient
            ->method('getPhotos')
            ->willReturn($photoData);

        $this->entityManager
            ->expects($this->exactly(3))
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->sut->importPhotosForUser($user);

        // Assert
        $this->assertSame(3, $result);
    }

    // ==================== Duplicate Handling Tests ====================

    public function testImportPhotosSkipsDuplicates(): void
    {
        // Arrange
        $existingPhoto = new Photo();
        $existingPhoto->setImageUrl('https://example.com/existing.jpg');

        $user = $this->createUserWithToken('valid-token', [$existingPhoto]);

        $photoData = [
            ['photo_url' => 'https://example.com/existing.jpg'], // Duplicate
            ['photo_url' => 'https://example.com/new.jpg'],      // New
        ];

        $this->phoenixClient
            ->method('validateToken')
            ->willReturn(true);

        $this->phoenixClient
            ->method('getPhotos')
            ->willReturn($photoData);

        $this->entityManager
            ->expects($this->once()) // Only one photo should be persisted
            ->method('persist')
            ->with($this->callback(static function (Photo $photo): bool {
                return 'https://example.com/new.jpg' === $photo->getImageUrl();
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->sut->importPhotosForUser($user);

        // Assert
        $this->assertSame(1, $result);
    }

    public function testImportPhotosSkipsAllWhenAllAreDuplicates(): void
    {
        // Arrange
        $existingPhoto1 = new Photo();
        $existingPhoto1->setImageUrl('https://example.com/photo1.jpg');

        $existingPhoto2 = new Photo();
        $existingPhoto2->setImageUrl('https://example.com/photo2.jpg');

        $user = $this->createUserWithToken('valid-token', [$existingPhoto1, $existingPhoto2]);

        $photoData = [
            ['photo_url' => 'https://example.com/photo1.jpg'],
            ['photo_url' => 'https://example.com/photo2.jpg'],
        ];

        $this->phoenixClient
            ->method('validateToken')
            ->willReturn(true);

        $this->phoenixClient
            ->method('getPhotos')
            ->willReturn($photoData);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->sut->importPhotosForUser($user);

        // Assert
        $this->assertSame(0, $result);
    }

    // ==================== Data Mapping Tests ====================

    public function testImportPhotosHandlesMissingOptionalFields(): void
    {
        // Arrange
        $user = $this->createUserWithToken('valid-token');
        $photoData = [
            ['photo_url' => 'https://example.com/minimal.jpg'],
        ];

        $this->phoenixClient
            ->method('validateToken')
            ->willReturn(true);

        $this->phoenixClient
            ->method('getPhotos')
            ->willReturn($photoData);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (Photo $photo): bool {
                return 'https://example.com/minimal.jpg' === $photo->getImageUrl()
                    && null === $photo->getLocation()
                    && null === $photo->getDescription()
                    && null === $photo->getCamera()
                    && null === $photo->getTakenAt();
            }));

        // Act
        $result = $this->sut->importPhotosForUser($user);

        // Assert
        $this->assertSame(1, $result);
    }

    public function testImportPhotosSkipsPhotoWithoutUrl(): void
    {
        // Arrange
        $user = $this->createUserWithToken('valid-token');
        $photoData = [
            ['location' => 'Warsaw'], // No photo_url
            ['photo_url' => null],     // Null photo_url
            ['photo_url' => 'https://example.com/valid.jpg'],
        ];

        $this->phoenixClient
            ->method('validateToken')
            ->willReturn(true);

        $this->phoenixClient
            ->method('getPhotos')
            ->willReturn($photoData);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (Photo $photo): bool {
                return 'https://example.com/valid.jpg' === $photo->getImageUrl();
            }));

        // Act
        $result = $this->sut->importPhotosForUser($user);

        // Assert
        $this->assertSame(1, $result);
    }

    #[DataProvider('partialDataProvider')]
    public function testImportPhotosHandlesPartialData(
        array $inputData,
        ?string $expectedLocation,
        ?string $expectedDescription,
        ?string $expectedCamera
    ): void {
        // Arrange
        $user = $this->createUserWithToken('valid-token');

        $this->phoenixClient
            ->method('validateToken')
            ->willReturn(true);

        $this->phoenixClient
            ->method('getPhotos')
            ->willReturn([$inputData]);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (Photo $photo) use ($expectedLocation, $expectedDescription, $expectedCamera): bool {
                return $photo->getLocation() === $expectedLocation
                    && $photo->getDescription() === $expectedDescription
                    && $photo->getCamera() === $expectedCamera;
            }));

        // Act
        $result = $this->sut->importPhotosForUser($user);

        // Assert
        $this->assertSame(1, $result);
    }

    /**
     * @return iterable<string, array{inputData: array<string, mixed>, expectedLocation: ?string, expectedDescription: ?string, expectedCamera: ?string}>
     */
    public static function partialDataProvider(): iterable
    {
        yield 'only location' => [
            'inputData' => [
                'photo_url' => 'https://example.com/photo.jpg',
                'location' => 'Kraków',
            ],
            'expectedLocation' => 'Kraków',
            'expectedDescription' => null,
            'expectedCamera' => null,
        ];

        yield 'only description' => [
            'inputData' => [
                'photo_url' => 'https://example.com/photo.jpg',
                'description' => 'Amazing view',
            ],
            'expectedLocation' => null,
            'expectedDescription' => 'Amazing view',
            'expectedCamera' => null,
        ];

        yield 'only camera' => [
            'inputData' => [
                'photo_url' => 'https://example.com/photo.jpg',
                'camera' => 'Sony A7III',
            ],
            'expectedLocation' => null,
            'expectedDescription' => null,
            'expectedCamera' => 'Sony A7III',
        ];

        yield 'location and camera' => [
            'inputData' => [
                'photo_url' => 'https://example.com/photo.jpg',
                'location' => 'Gdańsk',
                'camera' => 'Nikon Z6',
            ],
            'expectedLocation' => 'Gdańsk',
            'expectedDescription' => null,
            'expectedCamera' => 'Nikon Z6',
        ];
    }

    // ==================== Helper Methods ====================

    /**
     * @param Photo[] $existingPhotos
     */
    private function createUserWithToken(?string $token, array $existingPhotos = []): User
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        $user->setPhoenixApiToken($token);

        foreach ($existingPhotos as $photo) {
            $photo->setUser($user);
            $user->addPhoto($photo);
        }

        return $user;
    }
}
