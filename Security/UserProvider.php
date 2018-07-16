<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Security;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\User\APIUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * eZ User provider decorator.
 * Allows to fetch users using e-mail, in addition to traditional username.
 */
class UserProvider implements APIUserProviderInterface
{
    use EmailAuthenticationActivationChecker;

    /**
     * @var APIUserProviderInterface
     */
    private $innerUserProvider;

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(APIUserProviderInterface $innerUserProvider, UserService $userService)
    {
        $this->innerUserProvider = $innerUserProvider;
        $this->userService = $userService;
    }

    public function loadUserByUsername($username)
    {
        try {
            return $this->innerUserProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            if (!$this->isEmailAuthenticationEnabled()) {
                throw $e;
            }

            $users = $this->userService->loadUsersByEmail($username);
            if (empty($users)) {
                throw new UsernameNotFoundException("Could not find a user with idenfifier $username");
            }

            return $this->loadUserByAPIUser(reset($users));
        }
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->innerUserProvider->refreshUser($user);
    }

    public function supportsClass($class)
    {
        return $this->innerUserProvider->supportsClass($class);
    }

    public function loadUserByAPIUser(APIUser $apiUser)
    {
        return $this->innerUserProvider->loadUserByAPIUser($apiUser);
    }
}
