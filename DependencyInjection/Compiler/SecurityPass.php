<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\DependencyInjection\Compiler;

use Lolautruche\EzCoreExtraBundle\Security\RepositoryAuthenticationProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SecurityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('security.authentication.provider.dao')) {
            return;
        }

        $container->findDefinition('security.authentication.provider.dao')
            ->setClass(RepositoryAuthenticationProvider::class)
            ->addMethodCall('setConfigResolver', [new Reference('ezpublish.config.resolver')]);
    }
}
