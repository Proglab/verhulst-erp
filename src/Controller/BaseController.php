<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @method User|null getUser()
 */
class BaseController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
            EntityManagerInterface::class => '?' . EntityManagerInterface::class,
            RequestStack::class => '?' . RequestStack::class,
        ];
    }

    /**
     * @throws \JsonException
     */
    protected function json_decode_content_request(Request $request, bool $assoc = true): mixed
    {
        return json_decode($request->getContent(), $assoc, 512, \JSON_THROW_ON_ERROR);
    }

    protected function setElementValueIfFormNotValid(FormInterface $form, string $element, string $newValue): string
    {
        if ($form->isSubmitted() && !$form->isValid()) {
            return $newValue;
        }

        return $element;
    }

    protected function addCustomFlash(string $type, string $label, string $message): void
    {
        $this->container->get(RequestStack::class)->getSession()->getFlashBag()->add($type, ['label' => $label, 'message' => $message]);
    }
}
