<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Budget\Event;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CanUpdateEventVoter extends Voter
{
    public const EDIT = 'event-edit';
    public const UNARCHIVE = 'event-unarchive';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return \in_array($attribute, [self::EDIT, self::UNARCHIVE], true)
            && $subject instanceof Event;
    }

    /**
     * @param Event $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (self::UNARCHIVE !== $attribute) {
            if ($subject->isArchived()) {
                return false;
            }
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($subject->getAdmin()->getId() === $user->getId()) {
            return true;
        }

        if ($subject->getUsers()->contains($user)) {
            return true;
        }

        return false;
    }
}
