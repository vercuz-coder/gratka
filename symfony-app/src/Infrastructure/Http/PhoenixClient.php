<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Port\PhoenixClientInterface;
use Exception;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PhoenixClient implements PhoenixClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $baseUrl
    ) {
    }

    public function getPhotos(string $token): array
    {
        try {
            $response = $this->client->request('GET', $this->baseUrl.'/photos', [
                'headers' => [
                    'access-token' => $token,
                ],
            ]);

            if (200 !== $response->getStatusCode()) {
                return [];
            }

            $data = $response->toArray();

            return $data['photos'] ?? [];
        } catch (TransportExceptionInterface|Exception $e) {
            return [];
        }
    }

    public function validateToken(string $token): bool
    {
        try {
            // For now use existing photos endpoint to validate token
            $response = $this->client->request('GET', $this->baseUrl.'/photos', [
                'headers' => [
                    'access-token' => $token,
                ],
            ]);

            return 200 === $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }
}
