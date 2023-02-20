<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;

class DashboardPagesController extends DashboardController
{
    #[Route('/admin/{_locale}', name: 'dashboard_admin')]
    public function index(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('dashboard_com');
        }

        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/admin/{_locale}/dashboard/com', name: 'dashboard_com')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard_com.html.twig');
    }

    #[Route('/admin/{_locale}/mail', name: 'mailer')]
    public function mail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('erp@verhulst.pro')
            ->to('fabrice@proglab.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Test de mail')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');
        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse(['message' => $e->getMessage()]);
        }

        return new JsonResponse(['mail' => 'ok']);
    }
}
