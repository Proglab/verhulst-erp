<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class ProductController extends AbstractController
{
    #[Route('/app/{_locale}/projects/{project}/addEvent', name: 'project_add_event')]
    public function project_add_event(Request $request, string $_locale, Project $project): Response
    {
        return $this->render('app/projects/products/pages/create_event_page.html.twig', [
            'locale' => $_locale,
            'project' => $project,
        ]);
    }

    #[Route('/app/{_locale}/projects/{project}/addPackage', name: 'project_add_package')]
    public function project_add_package(Request $request, string $_locale, Project $project): Response
    {
        return $this->render('app/projects/products/pages/create_package_page.html.twig', [
            'locale' => $_locale,
            'project' => $project,
        ]);
    }

    #[Route('/app/{_locale}/projects/{project}/addSponsoring', name: 'project_add_sponsoring')]
    public function project_add_sponsoring(Request $request, string $_locale, Project $project): Response
    {
        return $this->render('app/projects/products/pages/create_sponsoring_page.html.twig', [
            'locale' => $_locale,
            'project' => $project,
        ]);
    }

    #[Route('/app/{_locale}/projects/{project}/addDivers', name: 'project_add_divers')]
    public function project_add_divers(Request $request, string $_locale, Project $project): Response
    {
        return $this->render('app/projects/products/pages/create_divers_page.html.twig', [
            'locale' => $_locale,
            'project' => $project,
        ]);
    }
}
