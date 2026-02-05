<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Port\PhoenixClientInterface;
use App\Entity\User;
use App\Form\UserProfileType;
use App\Service\PhotoImportService;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly PhoenixClientInterface $phoenixClient,
        private readonly EntityManagerInterface $em,
        private readonly PhotoImportService $photoImportService
    ) {
    }

    #[Route('/profile', name: 'profile', methods: ['GET', 'POST'])]
    public function profile(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $user->getPhoenixApiToken();

            if ($token) {
                // Validate token before saving
                if (!$this->phoenixClient->validateToken($token)) {
                    $this->addFlash('error', 'Wrong access token. Please check your token and try again.');

                    return $this->redirectToRoute('profile');
                }
            }

            $this->em->flush();
            $this->addFlash('success', 'Token saved successfully.');

            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/import-photos', name: 'profile_import_photos', methods: ['POST'])]
    public function importPhotos(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $importedCount = $this->photoImportService->importPhotosForUser($user);

            if ($importedCount > 0) {
                $this->addFlash('success', \sprintf('Successfully imported %d photo(s).', $importedCount));
            } else {
                $this->addFlash('info', 'All photos have already been imported.');
            }
        } catch (RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('profile');
    }
}
