<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass registers view parameter providers.
 */
class ParameterProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ez_core_extra.view_template_listener')) {
            return;
        }

        $viewTemplateListenerDef = $container->findDefinition('ez_core_extra.view_template_listener');
        foreach ($container->findTaggedServiceIds('ez_core_extra.view_parameter_provider') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        'ez_core_extra.view_parameter_provider service tag needs an "alias" attribute to ' .
                        'identify the parameter provider. None given.'
                    );
                }

                $viewTemplateListenerDef->addMethodCall('addParameterProvider', [new Reference($id), $attribute['alias']]);
            }
        }
    }
}
