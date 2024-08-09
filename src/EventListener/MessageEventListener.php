<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

class MessageEventListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly string $mailerFrom,
        private readonly string $mailerFromLabel,
        private readonly string $mailerSimulation,
        private readonly EntrypointLookupInterface $entrypointLookup,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }

    public function onMessage(MessageEvent $event): void
    {
        if (!$event->isQueued()) {
            $this->entrypointLookup->reset();
        }

        $email = $event->getMessage();
        if (!$email instanceof Email) {
            return;
        }

        $email->from(new Address($this->mailerFrom, $this->mailerFromLabel));

        if ($this->mailerSimulation) {
            $email->to(new Address($this->mailerFrom, $this->mailerFromLabel));
        }

        $email->subject(\sprintf(
            '%s - %s',
            $this->translator->trans('layout.site_name'),
            $email->getSubject()
        ));
    }
}
