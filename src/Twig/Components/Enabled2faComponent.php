<?php
declare(strict_types=1);

namespace App\Twig\Components;
use App\Entity\User;
use App\Form\Model\DoubleFactorAuthenticationSetup;
use App\Form\Type\DoubleFactorAuthenticationSetupType;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('enabled_2fa')]
class Enabled2faComponent
{
    public string $redirectUrl;

    public function __construct(
        private readonly Security $security,
        private readonly TotpAuthenticatorInterface $totpAuthenticator,
        private readonly CacheInterface $cache,
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator
    )
    {
    }


    public function getForm(): FormView|string
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user->isTotpAuthenticationEnabled()) {
            $totpAuthenticator = $this->totpAuthenticator;
            $totpCode = $this->cache->get(sprintf('2fa_activation_totp_%s', str_replace('@', '', $user->getEmail())),
                function (ItemInterface $item) use ($totpAuthenticator) {
                    $item->expiresAfter(900);
                    return $totpAuthenticator->generateSecret();
                });
            $user->setTotpSecret($totpCode);
            $this->entityManager->flush();
        }

        $setup = new DoubleFactorAuthenticationSetup();
        $setup->user = $user;
        $form = $this->formFactory->create(DoubleFactorAuthenticationSetupType::class, $setup);
        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setIsTotpEnabled(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->cache->delete(sprintf('2fa_activation_totp_%s', str_replace('@', '', $user->getEmail())));
            $this->cache->delete(sprintf('2fa_activation_qr_code_%s', str_replace('@', '', $user->getEmail())));
            /** @var Session $session */
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('success', $this->translator->trans('2fa.enable.success_message'));
            return '<script>window.location.href = \''.$this->redirectUrl.'\';</script>';
        }
        return $form->createView();
    }
}