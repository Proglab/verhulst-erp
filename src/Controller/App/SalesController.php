<?php

namespace App\Controller\App;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class SalesController extends AbstractController
{
    #[Route('/app/{_locale}/sales/recap', name: 'sales_recap')]
    public function create()
    {
        return $this->render('app/sales/recap.html.twig');
    }

    #[Route('/app/{_locale}/sales/recap/download/{filename}', name: 'download_file')]
    public function download_file($filename)
    {
        $file = $this->getParameter('kernel.project_dir') . '/public/' . $filename;
        return  $this->file($file);
    }
}