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
    const IBEXA_ROLE_PREFIX = 'ibexa:';

    /**
     * @var VoterInterface
     */
    private $coreVoter;

    /**
     * @var VoterInterface
     */
    private $valueObjectVoter;

    public function __construct(VoterInterface $coreVoter, VoterInterface $valueObjectVoter)
    {
        $this->coreVoter = $coreVoter;
        $this->valueObjectVoter = $valueObjectVoter;
    }

    public function supportsAttribute($attribute)
    {
        return is_string($attribute) && stripos($attribute, static::IBEXA_ROLE_PREFIX) === 0;
    }

    public function supportsClass($class)
    {
        return true;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $attribute = substr($attribute, strlen(static::IBEXA_ROLE_PREFIX));
            list($module, $function) = explode(':', $attribute);
            $attributeObject = new AuthorizationAttribute($module, $function);
            try {
                if ($object instanceof ValueObject) {
                    $attributeObject->limitations = ['valueObject' => $object];
                    return $this->valueObjectVoter->vote($token, $object, [$attributeObject]);
                } else {
                    return $this->coreVoter->vote($token, $object, [$attributeObject]);
                }
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }

        return static::ACCESS_ABSTAIN;
    }
}
