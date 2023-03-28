<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductVoter extends Voter
{
    public const CAN_ADD_PRODUCT = 'CAN_ADD_PRODUCT';

    public function __construct(private RoleHierarchyInterface $roleHierarchy)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::CAN_ADD_PRODUCT], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $roles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

        if (!\in_array('ROLE_ENCODE', $roles, true) && \in_array('ROLE_COMMERCIAL', $roles, true)) {
            return true;
        }

        return false;
    }
}
