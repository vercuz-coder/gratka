<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Port\PhoenixClientInterface;
use App\Entity\Photo;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class PhotoImportService
{
    public function __construct(
        private readonly PhoenixClientInterface $phoenixClient,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function importPhotosForUser(User $user): int
    {
        $token = $user->getPhoenixApiToken();

        if (!$token) {
            throw new RuntimeException('Please set your Phoenix API token first.');
        }

        // Validate token
        if (!$this->phoenixClient->validateToken($token)) {
            throw new RuntimeException('Wrong access token. Please check your token and try again.');
        }

        // Fetch photos from Phoenix API
        $photos = $this->phoenixClient->getPhotos($token);

        if (empty($photos)) {
            return 0;
        }

        // Get existing photo URLs for this user to check duplicates
        $existingUrls = [];
        foreach ($user->getPhotos() as $photo) {
            $existingUrls[$photo->getImageUrl()] = true;
        }

        $importedCount = 0;

        foreach ($photos as $photoData) {
            $photoUrl = $photoData['photo_url'] ?? null;

            if (!$photoUrl) {
                continue;
            }

            // Skip duplicates
            if (isset($existingUrls[$photoUrl])) {
                continue;
            }

            $photo = new Photo();
            $photo->setImageUrl($photoUrl);
            $photo->setUser($user);
            $photo->setLocation($photoData['location'] ?? null);
            $photo->setDescription($photoData['description'] ?? null);
            $photo->setCamera($photoData['camera'] ?? null);

            if (!empty($photoData['taken_at'])) {
                $photo->setTakenAt(new DateTimeImmutable($photoData['taken_at']));
            }

            $this->em->persist($photo);
            $existingUrls[$photoUrl] = true;
            ++$importedCount;
        }

        $this->em->flush();

        return $importedCount;
    }
}
