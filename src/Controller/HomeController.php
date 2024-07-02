<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\InvoiceGetter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        return $this->redirectToRoute('admin', ['_locale' => $request->getLocale()]);
    }

    /**
     * #[Route('/dashboard', name: 'dashboard')]
     * public function test(InvoiceGetter $invoiceGetter): Response
     * {
     * $invoices = $invoiceGetter->getInvoices();.
     *
     * dd($invoices);
     *
     * return $this->json($invoices);
     * }
     **/
    #[Route('/testlayout', name: 'testlayout')]
    public function testLayout(): Response
    {
        return $this->render('app/test/index.html.twig');
    }
}
