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

use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ez_core_extra');

        $systemNode = $this->generateScopeBaseNode($treeBuilder->getRootNode());
        $systemNode
            ->arrayNode('twig_globals')
                ->info('Variables available in all Twig templates for current SiteAccess.')
                ->normalizeKeys(false)
                ->useAttributeAsKey('variable_name')
                ->example(array('foo' => '"bar"', 'pi' => 3.14))
                ->prototype('variable')->end()
            ->end();

        return $treeBuilder;
    }
}
