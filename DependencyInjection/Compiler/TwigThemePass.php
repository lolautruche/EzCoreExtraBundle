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

use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

/**
 * Registers defined designs as valid Twig namespaces.
 * A design is a collection of ordered themes (in fallback order).
 * A theme is a collection of one or several template paths.
 */
class TwigThemePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!($container->hasParameter('kernel.bundles') && $container->hasDefinition('twig.loader.filesystem'))) {
            return;
        }

        $themesPathMap = [
            '_override' => array_merge(
                [$container->getParameter('kernel.root_dir').'/Resources/views'],
                $container->getParameter('ez_core_extra.themes.override_paths')
            )
        ];
        $finder = new Finder();
        foreach ($container->getParameter('kernel.bundles') as $bundleName => $bundleClass) {
            $bundleReflection = new ReflectionClass($bundleClass);
            $bundleViewsDir = dirname($bundleReflection->getFileName()) . '/Resources/views';
            $themeDir = $bundleViewsDir.'/themes';
            if (!is_dir($themeDir)) {
                continue;
            }

            /** @var \Symfony\Component\Finder\SplFileInfo $directoryInfo */
            foreach ($finder->directories()->in($themeDir) as $directoryInfo) {
                $themesPathMap[$directoryInfo->getBasename()][] = $directoryInfo->getRealPath();
            }
        }

        $twigLoaderDef = $container->findDefinition('twig.loader.filesystem');
        // Add application theme directory for each theme.
        foreach ($themesPathMap as $theme => &$paths) {
            if ($theme === '_override') {
                continue;
            }

            $overrideThemeDir = $container->getParameter('kernel.root_dir') . "/Resources/views/themes/$theme";
            if (is_dir($overrideThemeDir)) {
                array_unshift($paths, $overrideThemeDir);
            }
        }

        foreach ($container->getParameter('ez_core_extra.themes.design_list') as $designName => $themeFallback) {
            // Always add _override theme first.
            array_unshift($themeFallback, '_override');
            foreach ($themeFallback as $theme) {
                // Theme could not be found in expected directories, just ignore.
                if (!isset($themesPathMap[$theme])) {
                    continue;
                }

                foreach ($themesPathMap[$theme] as $path) {
                    $twigLoaderDef->addMethodCall('addPath', [$path, $designName]);
                }
            }
        }

        $container->setParameter('ez_core_extra.themes.list', array_keys($themesPathMap));
        $container->setParameter('ez_core_extra.themes.path_map', $themesPathMap);
    }
}
