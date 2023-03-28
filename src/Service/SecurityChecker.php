<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class SecurityChecker
{
    public function __construct(private AccessDecisionManagerInterface $accessDecisionManager)
    {
    }

    public function isGrantedByUser(User $user, string $attribute, object $object = null): bool
    {
        $token = new UsernamePasswordToken($user, 'none', $user->getRoles());

        return $this->accessDecisionManager->decide($token, [$attribute], $object);
    }

    public function isGrantedByRole(string|array $role, string $attribute, object $object = null): bool
    {
        $user = new User();
        if (\is_array($role)) {
            $user->setRoles($role);
        } else {
            $user->setRoles([$role]);
        }
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        return $this->accessDecisionManager->decide($token, [$attribute], $object);
    }
}
