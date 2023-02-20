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
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "erp@verhulst.pro",
                        'Name' => "verhulst.pro"
                    ],
                    'To' => [
                        [
                            'Email' => "info@proglab.com",
                            'Name' => "Fabrice"
                        ]
                    ],
                    'Subject' => "Greetings from Mailjet.",
                    'HTMLPart' => "<h3>Dear User, welcome to Mailjet!</h3><br />May the delivery force be with you!"
                ]
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3.1/send");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json')
        );
        curl_setopt($ch, CURLOPT_USERPWD, "669e4d298a8a40cd24523ab3bc3c598b:b92be50960a11ff4c03f6bbab91945b3");
        $server_output = curl_exec($ch);
        curl_close ($ch);

        $response = json_decode($server_output);
        dd($response);
        if ($response->Messages[0]->Status == 'success') {
            echo "Email sent successfully.";
        }
    }
}
