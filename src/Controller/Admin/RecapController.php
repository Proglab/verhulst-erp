<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class RecapController extends DashboardController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/admin/{_locale}/recap', name: 'app_admin_recap')]
    public function recap(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new UnauthorizedHttpException('Unauthorized');
        }

        /** @var UserRepository $repo */
        $repo = $this->entityManager->getRepository(User::class);
        $users = $repo->getCommercials();

        return $this->render('admin/recap/recap.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/{_locale}/myrecap', name: 'app_admin_myrecap')]
    public function myRecap(): Response
    {
        $user = $this->getUser();

        return $this->render('admin/recap/myrecap.html.twig', [
            'user' => $user,
        ]);
    }
}
