<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ez_core_extra');

        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->root('ez_core_extra');
        } else {
            $rootNode = $treeBuilder->getRootNode();
        }

        $systemNode = $this->generateScopeBaseNode($rootNode);
        $systemNode
            ->arrayNode('twig_globals')
                ->info('Variables available in all Twig templates for current SiteAccess.')
                ->normalizeKeys(false)
                ->useAttributeAsKey('variable_name')
                ->example(array('foo' => '"bar"', 'pi' => 3.14))
                ->prototype('variable')->end()
            ->end()
            ->booleanNode('enable_email_authentication')
                ->info('Whether eZ users can authenticate against their e-mail or not.')
                ->defaultFalse()
            ->end();

        return $treeBuilder;
    }
}
