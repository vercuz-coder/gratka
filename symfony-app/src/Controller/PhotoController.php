<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Service\LikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotoController extends AbstractController
{
    public function __construct(
        private readonly LikeRepository $likeRepository,
        private readonly LikeService $likeService,
    ) {}

    #[Route('/photo/{id}/like', name: 'photo_like', methods: ['POST'])]
    public function like(Photo $photo): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $existingLike = $this->likeRepository->findOneByUserAndPhoto($user, $photo);

        if ($existingLike) {
            $this->likeService->removeLike($photo, $user);
            $liked = false;
        } else {
            $this->likeService->addLike($photo, $user);
            $liked = true;
        }

        return $this->render('home/_like_button.html.twig', [
            'photo' => $photo,
            'liked' => $liked,
        ]);
    }
}
