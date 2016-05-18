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

class TwigThemePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!($container->hasParameter('kernel.bundles') && $container->hasDefinition('twig.loader.filesystem'))) {
            return;
        }

        $themesPathMap = [];
        $finder = new Finder();
        foreach ($container->getParameter('kernel.bundles') as $bundleName => $bundleClass) {
            $bundleReflection = new ReflectionClass($bundleClass);
            $bundleViewsDir = dirname($bundleReflection->getFileName()) . '/Resources/views';
            $themeDir = $bundleViewsDir.'/themes';
            // Theme directory is not present in the bundle, only register the regular views directory for "_base" theme.
            if (!is_dir($themeDir)) {
                $themesPathMap['_base'][] = $bundleViewsDir;
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
            array_unshift($paths, "%kernel.root_dir%/Resources/views/themes/$theme");
        }

        $designThemesFallbacks = [];
        // TODO: Define ez_core_extra.themes.design_list in DIC extension, from semantic config
        foreach ($container->getParameter('ez_core_extra.themes.design_list') as $designName => $themeFallback) {
            foreach ($themeFallback as $theme) {
                if (!isset($themesPathMap[$theme])) {
                    // TODO: Should we throw an exception?
                    continue;
                }

                foreach ($themesPathMap[$theme] as $path) {
                    $designThemesFallbacks[$designName][] = $path;
                    $twigLoaderDef->addMethodCall('addPath', [$path, $designName]);
                }
            }
        }

        $container->setParameter('ez_core_extra.themes.design_themes_fallbacks', $designThemesFallbacks);
        $container->setParameter('ez_core_extra.themes.list', array_keys($themesPathMap));
        $container->setParameter('ez_core_extra.themes.path_map', $themesPathMap);
    }
}
