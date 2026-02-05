<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\PhotoFilter;
use App\Entity\User;
use App\Form\PhotoFilterType;
use App\Repository\LikeRepository;
use App\Repository\PhotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly PhotoRepository $photoRepository,
        private readonly LikeRepository $likeRepository,
    ) {}

    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        $filter = new PhotoFilter();
        $filterForm = $this->createForm(PhotoFilterType::class, $filter);
        $filterForm->handleRequest($request);

        $photos = $this->photoRepository->findFiltered($filter);

        $currentUser = $this->getUser();
        $userLikes = [];

        if ($currentUser instanceof User) {
            foreach ($photos as $photo) {
                $userLikes[$photo->getId()] = null !== $this->likeRepository->findOneByUserAndPhoto($currentUser, $photo);
            }
        }

        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'currentUser' => $currentUser,
            'userLikes' => $userLikes,
            'filterForm' => $filterForm->createView(),
        ]);
    }
}
