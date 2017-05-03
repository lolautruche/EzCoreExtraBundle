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

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

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

        $resolver = $container->get('ez_theme.asset_path_resolver.runtime');
        $webrootDir = realpath($container->getParameter('webroot_dir'));
        $resolvedPathsByDesign = [];
        foreach ($container->getParameter('ez_core_extra.themes.assets_path_map') as $design => $paths) {
            $assetsLogicalPaths = [];
            foreach ($paths as $path) {
                $themePath = "$webrootDir/$path";
                /** @var \SplFileInfo $fileInfo */
                foreach ((new Finder())->files()->in($themePath)->followLinks()->ignoreUnreadableDirs() as $fileInfo) {
                    $assetsLogicalPaths[] = trim(substr($fileInfo->getPathname(), strlen($themePath)), '/');
                }
            }

            foreach (array_unique($assetsLogicalPaths) as $logicalPath) {
                $resolvedPathsByDesign[$design][$logicalPath] = $resolver->resolveAssetPath($logicalPath, $design);
            }
        }

        $container->setParameter('ez_theme.asset_resolved_paths', $resolvedPathsByDesign);
        $container->findDefinition('ez_theme.asset_path_resolver.provisioned')->replaceArgument(0, $resolvedPathsByDesign);
        $container->setAlias('ez_theme.asset_path_resolver', new Alias('ez_theme.asset_path_resolver.provisioned'));
    }
}
