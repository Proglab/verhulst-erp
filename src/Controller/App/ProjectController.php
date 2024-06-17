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
class ProjectController extends AbstractController
{
    public function __construct()
    {

    }

    #[Route('/app/{_locale}/projects/new', name: 'project_new')]
    public function project_new(Request $request, string $_locale): Response
    {
        return $this->render('app/projects/pages/new.html.twig', [
            'locale' => $_locale,
        ]);
    }

    #[Route('/app/{_locale}/projects/index', name: 'project_index')]
    public function project_index(string $_locale): Response
    {
        return $this->render('app/projects/pages/index.html.twig', [
            'locale' => $_locale,
        ]);
    }
    #[Route('/app/{_locale}/projects/{project}', name: 'project_details')]
    public function project_details(Request $request, string $_locale, Project $project): Response
    {
        return $this->render('app/projects/pages/details.html.twig', [
            'locale' => $_locale,
            'project' => $project,
        ]);
    }

}
