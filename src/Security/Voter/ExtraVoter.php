<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ExtraVoter extends Voter
{
    final public const IS_NOT_AUTHENTICATED = 'is_not_authenticated';
    final public const IS_XML_HTTP_REQUEST = 'isXmlHttpRequest';

    protected array $attributes = [
        self::IS_NOT_AUTHENTICATED,
        self::IS_XML_HTTP_REQUEST,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, $this->attributes, true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($token instanceof NullToken) {
            switch ($attribute) {
                case self::IS_NOT_AUTHENTICATED:
                    return true;

                case self::IS_XML_HTTP_REQUEST:
                    return $this->isXmlHttpRequest($subject);
            }
        }

        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        return match ($attribute) {
            self::IS_XML_HTTP_REQUEST => $this->isXmlHttpRequest($subject),
            default => false,
        };
    }

    private function isXmlHttpRequest(Request $request): bool
    {
        return $request->isXmlHttpRequest();
    }
}
