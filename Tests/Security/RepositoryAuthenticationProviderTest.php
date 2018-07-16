<?php
/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Tests\Security;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\Values\User\User as APIUser;
use Lolautruche\EzCoreExtraBundle\Security\RepositoryAuthenticationProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use eZ\Publish\Core\MVC\Symfony\Security\User;

class RepositoryAuthenticationProviderTest extends TestCase
{
    /**
     * @var MockObject|Repository
     */
    private $repository;

    /**
     * @var MockObject|UserService
     */
    private $userService;

    /**
     * @var MockObject|ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var RepositoryAuthenticationProvider
     */
    private $authProvider;

    protected function setUp()
    {
        parent::setUp();
        $this->userService = $this->createMock(UserService::class);
        $this->repository = $this->createMock(Repository::class);
        $this->repository
            ->method('getUserService')
            ->willReturn($this->userService);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);

        $this->authProvider = new RepositoryAuthenticationProvider(
            $this->createMock(UserProviderInterface::class),
            $this->createMock(UserCheckerInterface::class),
            'ezpublish_front',
            $this->createMock(EncoderFactoryInterface::class)
        );
        $this->authProvider->setRepository($this->repository);
        $this->authProvider->setConfigResolver($this->configResolver);
    }

    public function testCheckAuthenticationUsername()
    {
        $user = $this->createMock(User::class);
        $userName = 'my_username';
        $password = 'foo';
        $token = new UsernamePasswordToken($userName, $password, 'bar');

        $apiUser = $this->createMock(APIUser::class);
        $this->userService
            ->expects($this->once())
            ->method('loadUserByCredentials')
            ->with($userName, $password)
            ->willReturn($apiUser);
        $this->repository
            ->expects($this->once())
            ->method('setCurrentUser')
            ->with($apiUser);

        $method = new \ReflectionMethod($this->authProvider, 'checkAuthentication');
        $method->setAccessible(true);
        $method->invoke($this->authProvider, $user, $token);
    }

    public function testCheckAuthenticationEmail()
    {
        $username = 'lolautruche';
        $userEmail = 'jerome@code-rhapsodie.fr';
        $password = 'foo';
        $user = $this->createMock(User::class);
        $user
            ->method('getUsername')
            ->willReturn($username);
        $token = new UsernamePasswordToken($userEmail, $password, 'bar');

        $this->configResolver
            ->method('getParameter')
            ->with('security.authentication_email.enabled', 'ez_core_extra')
            ->willReturn(true);

        $apiUser = new APIUser(['login' => $username, 'email' => $userEmail]);
        // First call to UserService::loadUserByCredentials is done by original RepositoryAuthenticationProvider.
        // It's supposed to fail since it's an email, and thus to thrown a BadCredentialsException.
        $this->userService
            ->expects($this->at(0))
            ->method('loadUserByCredentials')
            ->with($userEmail, $password)
            ->willThrowException(new BadCredentialsException());
        // 2nd call is done by our authentication provider, using correct username.
        // This username is stored in $user, which is supposed to be returned by our user provider.
        $this->userService
            ->expects($this->at(1))
            ->method('loadUserByCredentials')
            ->with($username, $password)
            ->willReturn($apiUser);
        $this->repository
            ->expects($this->once())
            ->method('setCurrentUser')
            ->with($apiUser);

        $method = new \ReflectionMethod($this->authProvider, 'checkAuthentication');
        $method->setAccessible(true);
        $method->invoke($this->authProvider, $user, $token);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function testCheckAuthenticationBadEmail()
    {
        $username = 'lolautruche';
        $userEmail = 'jerome@code-rhapsodie.fr';
        $password = 'foo';
        $user = $this->createMock(User::class);
        $user
            ->method('getUsername')
            ->willReturn($username);
        $token = new UsernamePasswordToken($userEmail, $password, 'bar');

        $this->configResolver
            ->method('getParameter')
            ->with('security.authentication_email.enabled', 'ez_core_extra')
            ->willReturn(true);

        // First call to UserService::loadUserByCredentials is done by original RepositoryAuthenticationProvider.
        // It's supposed to fail since it's an email, and thus to thrown a BadCredentialsException.
        $this->userService
            ->expects($this->at(0))
            ->method('loadUserByCredentials')
            ->with($userEmail, $password)
            ->willThrowException(new BadCredentialsException());
        // 2nd call is done by our authentication provider, using correct username.
        // This username is stored in $user, which is supposed to be returned by our user provider.
        $this->userService
            ->expects($this->at(1))
            ->method('loadUserByCredentials')
            ->with($username, $password)
            ->willThrowException(new NotFoundException('Not found', 'User'));
        $this->repository
            ->expects($this->never())
            ->method('setCurrentUser');

        $method = new \ReflectionMethod($this->authProvider, 'checkAuthentication');
        $method->setAccessible(true);
        $method->invoke($this->authProvider, $user, $token);
    }

    /**
     * @@expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function testCheckAuthenticationEmailDisabled()
    {
        $username = 'lolautruche';
        $userEmail = 'jerome@code-rhapsodie.fr';
        $password = 'foo';
        $user = $this->createMock(User::class);
        $token = new UsernamePasswordToken($userEmail, $password, 'bar');

        $this->configResolver
            ->method('getParameter')
            ->with('security.authentication_email.enabled', 'ez_core_extra')
            ->willReturn(false);

        $this->userService
            ->expects($this->once())
            ->method('loadUserByCredentials')
            ->with($userEmail, $password)
            ->willThrowException(new BadCredentialsException());
        $this->repository
            ->expects($this->never())
            ->method('setCurrentUser');

        $method = new \ReflectionMethod($this->authProvider, 'checkAuthentication');
        $method->setAccessible(true);
        $method->invoke($this->authProvider, $user, $token);
    }
}
