<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Photo;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Photo>
 */
final class PhotoFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Photo::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'user' => UserFactory::new(),
            'imageUrl' => self::faker()->imageUrl(),
            'location' => self::faker()->city(),
            'description' => self::faker()->sentence(),
            'camera' => self::faker()->word(),
            'takenAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'likeCounter' => self::faker()->numberBetween(0, 100),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Photo $photo): void {})
        ;
    }
}
