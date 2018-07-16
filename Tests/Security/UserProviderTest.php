<?php
/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Tests\Security;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\Core\Repository\Values\User\User as APIUser;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use eZ\Publish\Core\MVC\Symfony\Security\User\APIUserProviderInterface;
use Lolautruche\EzCoreExtraBundle\Security\UserProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProviderTest extends TestCase
{
    /**
     * @var MockObject|APIUserProviderInterface
     */
    private $innerProvider;

    /**
     * @var MockObject|UserService
     */
    private $userService;

    /**
     * @var MockObject|ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var UserProvider
     */
    private $userProvider;

    public function setUp()
    {
        parent::setUp();
        $this->innerProvider = $this->createMock(APIUserProviderInterface::class);
        $this->userService = $this->createMock(UserService::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->userProvider = new UserProvider($this->innerProvider, $this->userService);
        $this->userProvider->setConfigResolver($this->configResolver);
    }

    public function testLoadUserByUsername()
    {
        $user = new User();
        $username = 'lolautruche';
        $this->innerProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($username)
            ->willReturn($user);

        $this->assertSame($user, $this->userProvider->loadUserByUsername($username));
    }

    public function testLoadUserByEmail()
    {
        $email = 'jerome@code-rhapsodie.fr';
        $this->innerProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($email)
            ->willThrowException(new UsernameNotFoundException());
        $this->configResolver
            ->method('getParameter')
            ->with('security.authentication_email.enabled', 'ez_core_extra')
            ->willReturn(true);

        $apiUser = $this->createMock(APIUser::class);
        $apiUser->method('getUserId')->willReturn(1);
        $user = new User($apiUser);
        $this->userService
            ->expects($this->once())
            ->method('loadUsersByEmail')
            ->with($email)
            ->willReturn([$apiUser]);
        $this->innerProvider
            ->expects($this->once())
            ->method('loadUserByAPIUser')
            ->with($apiUser)
            ->willReturn($user);

        $this->assertSame($user, $this->userProvider->loadUserByUsername($email));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserEmailNotFound()
    {
        $email = 'foo@bar.com';
        $this->innerProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($email)
            ->willThrowException(new UsernameNotFoundException());
        $this->configResolver
            ->method('getParameter')
            ->with('security.authentication_email.enabled', 'ez_core_extra')
            ->willReturn(true);
        $this->userService
            ->expects($this->once())
            ->method('loadUsersByEmail')
            ->with($email)
            ->willReturn([]);

        $this->userProvider->loadUserByUsername($email);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserEmailNotActivated()
    {
        $email = 'foo@bar.com';
        $this->innerProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($email)
            ->willThrowException(new UsernameNotFoundException());
        $this->configResolver
            ->method('getParameter')
            ->with('security.authentication_email.enabled', 'ez_core_extra')
            ->willReturn(false);

        $this->userProvider->loadUserByUsername($email);
    }
}
