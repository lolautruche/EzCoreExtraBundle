<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Security;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\RepositoryAuthenticationProvider as BaseProvider;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface as EzUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * eZ Repository authentication provider override.
 * Allows to authenticate against e-mail, in addition to traditional username.
 *
 * Original behavior is kept and always has precedence.
 */
class RepositoryAuthenticationProvider extends BaseProvider
{
    use EmailAuthenticationActivationChecker;

    /**
     * @var Repository
     */
    private $contentRepository;

    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    private $userService;

    public function setRepository(Repository $repository)
    {
        parent::setRepository($repository);
        $this->contentRepository = $repository;
        $this->userService = $repository->getUserService();
    }

    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        try {
            parent::checkAuthentication($user, $token);
        } catch (BadCredentialsException $e) {
            if (!($this->isEmailAuthenticationEnabled() && $user instanceof EzUserInterface)) {
                throw $e;
            }

            // This check was already made in parent implementation and really represents an exception, so rethrow it.
            if ($token->getUser() instanceof UserInterface) {
                throw $e;
            }

            try {
                $authenticatedRepoUser = $this->userService->loadUserByCredentials($user->getUsername(), $token->getCredentials());
                $this->contentRepository->setCurrentUser($authenticatedRepoUser);
            } catch (NotFoundException $exception) {
                throw new BadCredentialsException('Invalid credentials', 0, $e);
            }
        }
    }
}
