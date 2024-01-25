<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Budget\Event;
use App\Entity\CompanyContact;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CanUpdateImportContactVoter extends Voter
{
    public const EDIT = 'edit-import';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return \in_array($attribute, [self::EDIT], true)
            && 'Import' === $subject['type'];
    }

    /**
     * @param Event $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        /** @var CompanyContact $contact */
        $contact = $subject;

        if (empty($contact['added_by_id'])) {
            return true;
        }

        if ((int) $contact['added_by_id'] === $user->getId()) {
            return true;
        }

        return false;
    }
}
