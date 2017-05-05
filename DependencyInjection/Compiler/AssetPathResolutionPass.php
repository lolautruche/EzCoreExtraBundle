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

use Lolautruche\EzCoreExtraBundle\Asset\AssetPathProvisionerInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Resolves assets theme paths.
 * Avoids multiple I/O calls at runtime when looking for the right asset path.
 *
 * Will loop over registered theme paths for each design.
 * Within each theme path, will look for any files in order to make a list of all available assets.
 * Each asset is then regularly processed through the AssetPathResolver, like if it were called by asset() Twig function.
 */
class AssetPathResolutionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->getParameter('ez_theme.asset_resolution.disabled')) {
            return;
        }

        $resolvedPathsByDesign = $this->preResolveAssetsPaths(
            $container->get('ez_theme.asset_path_resolver.provisioned'),
            $container->getParameter('ez_core_extra.themes.assets_path_map')
        );

        $container->setParameter('ez_theme.asset_resolved_paths', $resolvedPathsByDesign);
        $container->findDefinition('ez_theme.asset_path_resolver.provisioned')->replaceArgument(0, $resolvedPathsByDesign);
        $container->setAlias('ez_theme.asset_path_resolver', new Alias('ez_theme.asset_path_resolver.provisioned'));
    }

    private function preResolveAssetsPaths(AssetPathProvisionerInterface $provisioner, array $designPathMap)
    {
        $resolvedPathsByDesign = [];
        foreach ($designPathMap as $design => $paths) {
            $resolvedPathsByDesign[$design] = $provisioner->provisionResolvedPaths($paths, $design);
        }

        return $resolvedPathsByDesign;
    }
}
