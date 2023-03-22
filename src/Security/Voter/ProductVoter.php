<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ProductVoter extends Voter
{
    public const CAN_ADD_PRODUCT = 'CAN_ADD_PRODUCT';

    public function __construct(private Security $security) {

    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CAN_ADD_PRODUCT]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$this->security->isGranted('ROLE_ENCODE') && $this->security->isGranted('ROLE_COMMERCIAL')) {
            return true;
        }

        return false;
    }
}
