<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Budget\Budget;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CanUpdateBudgetVoter extends Voter
{
    public const EDIT = 'budget-edit';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return \in_array($attribute, [self::EDIT], true)
            && $subject instanceof Budget;
    }

    /**
     * @param Budget $subject
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
            if ($subject->getEvent()->isArchived()) {
                return false;
            }
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    return true;
                }

                if ($subject->getEvent()->getAdmin()->getId() === $user->getId()) {
                    return true;
                }

                if ($subject->getEvent()->getUsers()->contains($user)) {
                    return true;
                }
                break;
        }

        return false;
    }
}
