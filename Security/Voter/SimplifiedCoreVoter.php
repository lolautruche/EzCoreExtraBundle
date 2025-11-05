<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Security\Voter;

use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SimplifiedCoreVoter implements VoterInterface
{
    const string IBEXA_ROLE_PREFIX = 'ibexa:';

    public function __construct(
        private VoterInterface $coreVoter,
        private VoterInterface $valueObjectVoter,
    ) {}

    public function supportsAttribute($attribute): bool
    {
        return is_string($attribute) && stripos($attribute, static::IBEXA_ROLE_PREFIX) === 0;
    }

    public function supportsClass($class): bool
    {
        return true;
    }

    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $attribute = substr($attribute, strlen(static::IBEXA_ROLE_PREFIX));
            [$module, $function] = explode(':', $attribute);
            $attributeObject = new AuthorizationAttribute($module, $function);
            try {
                if ($subject instanceof ValueObject) {
                    $attributeObject->limitations = ['valueObject' => $subject];
                    return $this->valueObjectVoter->vote($token, $subject, [$attributeObject]);
                }

                return $this->coreVoter->vote($token, $subject, [$attributeObject]);
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }

        return static::ACCESS_ABSTAIN;
    }
}
