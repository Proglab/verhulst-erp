<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Commission;
use App\Entity\Product;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommissionsController extends DashboardController
{
    public function __construct(private EntityManagerInterface $entityManager, private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    #[Route('/admin/{_locale}/commission', name: 'commission_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function com($_locale)
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $projects = $this->entityManager->getRepository(Project::class)->findAll();

        $comsData = $this->entityManager->getRepository(Commission::class)->findAll();

        $productsEvent = [];
        $productsPackage = [];
        $productsDivers = [];
        $productsSponsor = [];

        $commissions = [];

        /** @var Project $project */
        foreach ($projects as $project) {
            foreach ($project->getProductEvent() as $productEvent) {
                $productsEvent[] = $productEvent;
            }
            foreach ($project->getProductPackage() as $productPackage) {
                $productsPackage[] = $productPackage;
            }
            foreach ($project->getProductDivers() as $productDivers) {
                $productsDivers[] = $productDivers;
            }
            foreach ($project->getProductDivers() as $productSponsor) {
                $productsSponsor[] = $productSponsor;
            }
        }

        /** @var Commission $com */
        foreach ($comsData as $com) {
            $commissions[$com->getProduct()->getProject()->getId()][$com->getProduct()->getId()][$com->getUser()->getId()] = $com->getPercentCom();
        }

        return $this->render('admin/commission/index.html.twig', [
            'locale' => $_locale,
            'users' => $users,
            'commissions' => $commissions,
            'productsEvent' => $productsEvent,
            'productsPackage' => $productsPackage,
            'productsDivers' => $productsDivers,
            'productsSponsor' => $productsSponsor,
        ]);
    }

    #[Route('/admin/{_locale}/commission/{project_id}/{user_id}', name: 'commission_index_edit', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function comedit($_locale, $project_id, $user_id, Request $request)
    {
        $url = $this->generateUrl(
            'commission_index_edit', ['_locale' => $_locale, 'project_id' => $project_id, 'user_id' => $user_id]
        );

        $param = $request->request->get('com');
        if (!is_numeric($param)) {
            return $this->render('admin/commission/_input_percent.html.twig', [
                'url' => $url,
                'type' => 'error',
                'value' => $param,
            ]);
        }

        $user = $this->entityManager->getRepository(User::class)->find($user_id);
        $product = $this->entityManager->getRepository(Product::class)->find($project_id);

        $comRepo = $this->entityManager->getRepository(Commission::class);
        /** @var Commission $com */
        $com = $comRepo->findOneBy(['product' => $product, 'user' => $user]);

        if (empty($com)) {
            $com = new Commission();
            $com->setUser($user);
            $com->setProduct($product);
        }

        $com->setPercentCom($request->request->get('com'));

        $comRepo->save($com, true);

        return $this->render('admin/commission/_input_percent.html.twig', [
            'url' => $url,
            'type' => 0 === $com->getPercentCom() ? 'error' : '',
            'value' => $com->getPercentCom(),
        ]);
    }

    #[Route('/admin/{_locale}/commission/{project_id}', name: 'commission_index_edit_vr', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function comeditvr($_locale, $project_id, Request $request)
    {
        $url = $this->generateUrl(
            'commission_index_edit_vr', ['_locale' => $_locale, 'project_id' => $project_id]
        );

        $com = $request->request->get('com');
        if (!is_numeric($com)) {
            return $this->render('admin/commission/_input_percent.html.twig', [
                'url' => $url,
                'type' => 'error',
                'value' => $com,
            ]);
        }

        $productRepo = $this->entityManager->getRepository(Product::class);
        /** @var Product $product */
        $product = $productRepo->find($project_id);
        $product->setPercentVr($request->request->get('com'));
        $productRepo->save($product, true);

        return $this->render('admin/commission/_input_percent.html.twig', [
            'url' => $url,
            'type' => (0 === $product->getPercentVr() ? 'error' : ''),
            'value' => $product->getPercentVr(),
        ]);
    }
}
