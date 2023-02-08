<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\User;
use App\Form\Model\PasswordUpdate;
use App\Form\Type\PasswordUpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('update_password')]
class UpdatePasswordComponent
{
    public string $redirectUrl;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly FormFactoryInterface $formFactory,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getForm(): FormView|string
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $passwordUpdate = new PasswordUpdate();
        $form = $this->formFactory->create(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($this->requestStack->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $passwordUpdate->newPassword;
            $hash = $this->hasher->hashPassword($user, $newPassword);
            $user->setPassword($hash);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            /** @var Session $session */
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('success', $this->translator->trans('global.confirmation_message'));

            return '<script>window.location.href = \'' . $this->redirectUrl . '\';</script>';
        }

        return $form->createView();
    }
}
