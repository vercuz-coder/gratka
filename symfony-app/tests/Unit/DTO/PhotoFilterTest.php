<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\PhotoFilter;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PhotoFilter DTO.
 *
 * Tests cover:
 * - isEmpty() method with various field combinations
 * - Constructor initialization
 */
#[CoversClass(PhotoFilter::class)]
final class PhotoFilterTest extends TestCase
{
    // ==================== isEmpty() Tests ====================

    public function testIsEmptyReturnsTrueWhenAllFieldsAreNull(): void
    {
        // Arrange
        $filter = new PhotoFilter();

        // Act & Assert
        $this->assertTrue($filter->isEmpty());
    }

    public function testIsEmptyReturnsTrueWithExplicitNullValues(): void
    {
        // Arrange
        $filter = new PhotoFilter(
            location: null,
            camera: null,
            description: null,
            takenAtFrom: null,
            takenAtTo: null,
            username: null,
        );

        // Act & Assert
        $this->assertTrue($filter->isEmpty());
    }

    #[DataProvider('nonEmptyFilterProvider')]
    public function testIsEmptyReturnsFalseWhenAnyFieldIsSet(
        ?string $location,
        ?string $camera,
        ?string $description,
        ?DateTimeImmutable $takenAtFrom,
        ?DateTimeImmutable $takenAtTo,
        ?string $username,
    ): void {
        // Arrange
        $filter = new PhotoFilter(
            location: $location,
            camera: $camera,
            description: $description,
            takenAtFrom: $takenAtFrom,
            takenAtTo: $takenAtTo,
            username: $username,
        );

        // Act & Assert
        $this->assertFalse($filter->isEmpty());
    }

    /**
     * @return iterable<string, array{location: ?string, camera: ?string, description: ?string, takenAtFrom: ?DateTimeImmutable, takenAtTo: ?DateTimeImmutable, username: ?string}>
     */
    public static function nonEmptyFilterProvider(): iterable
    {
        yield 'only location set' => [
            'location' => 'Warsaw',
            'camera' => null,
            'description' => null,
            'takenAtFrom' => null,
            'takenAtTo' => null,
            'username' => null,
        ];

        yield 'only camera set' => [
            'location' => null,
            'camera' => 'Canon',
            'description' => null,
            'takenAtFrom' => null,
            'takenAtTo' => null,
            'username' => null,
        ];

        yield 'only description set' => [
            'location' => null,
            'camera' => null,
            'description' => 'Beautiful sunset',
            'takenAtFrom' => null,
            'takenAtTo' => null,
            'username' => null,
        ];

        yield 'only takenAtFrom set' => [
            'location' => null,
            'camera' => null,
            'description' => null,
            'takenAtFrom' => new DateTimeImmutable('2024-01-01'),
            'takenAtTo' => null,
            'username' => null,
        ];

        yield 'only takenAtTo set' => [
            'location' => null,
            'camera' => null,
            'description' => null,
            'takenAtFrom' => null,
            'takenAtTo' => new DateTimeImmutable('2024-12-31'),
            'username' => null,
        ];

        yield 'only username set' => [
            'location' => null,
            'camera' => null,
            'description' => null,
            'takenAtFrom' => null,
            'takenAtTo' => null,
            'username' => 'john_doe',
        ];

        yield 'multiple fields set' => [
            'location' => 'Kraków',
            'camera' => 'Sony A7',
            'description' => null,
            'takenAtFrom' => new DateTimeImmutable('2024-01-01'),
            'takenAtTo' => new DateTimeImmutable('2024-06-30'),
            'username' => 'photographer',
        ];

        yield 'all fields set' => [
            'location' => 'Gdańsk',
            'camera' => 'Nikon Z6',
            'description' => 'Beach photo',
            'takenAtFrom' => new DateTimeImmutable('2024-07-01'),
            'takenAtTo' => new DateTimeImmutable('2024-08-31'),
            'username' => 'pro_user',
        ];
    }

    // ==================== Constructor Tests ====================

    public function testConstructorInitializesAllFields(): void
    {
        // Arrange
        $takenAtFrom = new DateTimeImmutable('2024-01-15');
        $takenAtTo = new DateTimeImmutable('2024-06-15');

        // Act
        $filter = new PhotoFilter(
            location: 'Warsaw',
            camera: 'Canon EOS R5',
            description: 'City landscape',
            takenAtFrom: $takenAtFrom,
            takenAtTo: $takenAtTo,
            username: 'urban_photographer',
        );

        // Assert
        $this->assertSame('Warsaw', $filter->location);
        $this->assertSame('Canon EOS R5', $filter->camera);
        $this->assertSame('City landscape', $filter->description);
        $this->assertSame($takenAtFrom, $filter->takenAtFrom);
        $this->assertSame($takenAtTo, $filter->takenAtTo);
        $this->assertSame('urban_photographer', $filter->username);
    }

    public function testConstructorDefaultsToNullForAllFields(): void
    {
        // Act
        $filter = new PhotoFilter();

        // Assert
        $this->assertNull($filter->location);
        $this->assertNull($filter->camera);
        $this->assertNull($filter->description);
        $this->assertNull($filter->takenAtFrom);
        $this->assertNull($filter->takenAtTo);
        $this->assertNull($filter->username);
    }
}
