<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Form\Type\ProfileUpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('update_profile')]
class UpdateProfileComponent
{
    public string $redirectUrl;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getForm(): FormView|string
    {
        $user = $this->security->getUser();

        $form = $this->formFactory->create(ProfileUpdateType::class, $user);
        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();
            /** @var Session $session */
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add(
                'success',
                $this->translator->trans('global.confirmation_message')
            );

            return '<script>window.location.href = \'' . $this->redirectUrl . '\';</script>';
        }

        return $form->createView();
    }
}
