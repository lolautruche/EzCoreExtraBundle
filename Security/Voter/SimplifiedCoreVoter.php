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

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SimplifiedCoreVoter implements VoterInterface
{
    const EZ_ROLE_PREFIX = 'ez:';

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
        return is_string($attribute) && stripos($attribute, static::EZ_ROLE_PREFIX) === 0;
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

            $attribute = substr($attribute, strlen(static::EZ_ROLE_PREFIX));
            list($module, $function) = explode(':', $attribute);
            $attributeObject = new AuthorizationAttribute($module, $function);
            if ($object instanceof ValueObject) {
                $attributeObject->limitations = ['valueObject' => $object];
                return $this->valueObjectVoter->vote($token, $object, [$attributeObject]);
            } else {
                return $this->coreVoter->vote($token, $object, [$attributeObject]);
            }
        }

        return static::ACCESS_ABSTAIN;
    }
}
