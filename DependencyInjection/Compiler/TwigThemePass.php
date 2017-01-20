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

use Lolautruche\EzCoreExtraBundle\Templating\Twig\Profiler\Profile;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
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

        $globalViewsDir = $container->getParameter('kernel.root_dir').'/Resources/views';
        if (!is_dir($globalViewsDir)) {
            (new Filesystem())->mkdir($globalViewsDir);
        }
        $themesPathMap = [
            '_override' => array_merge(
                [$globalViewsDir],
                $container->getParameter('ez_core_extra.themes.override_paths')
            ),
        ];
        $finder = new Finder();
        foreach ($container->getParameter('kernel.bundles') as $bundleName => $bundleClass) {
            $bundleReflection = new ReflectionClass($bundleClass);
            $bundleViewsDir = dirname($bundleReflection->getFileName()).'/Resources/views';
            $themeDir = $bundleViewsDir.'/themes';
            if (!is_dir($themeDir)) {
                continue;
            }

            /** @var \Symfony\Component\Finder\SplFileInfo $directoryInfo */
            foreach ($finder->directories()->in($themeDir)->depth('== 0') as $directoryInfo) {
                $themesPathMap[$directoryInfo->getBasename()][] = $directoryInfo->getRealPath();
            }
        }

        $twigLoaderDef = $container->findDefinition('ez_core_extra.twig_theme_loader');
        // Add application theme directory for each theme.
        foreach ($themesPathMap as $theme => &$paths) {
            if ($theme === '_override') {
                continue;
            }

            $overrideThemeDir = $globalViewsDir."/themes/$theme";
            if (is_dir($overrideThemeDir)) {
                array_unshift($paths, $overrideThemeDir);
            }

            // De-duplicate the map
            $themesPathMap[$theme] = array_unique($themesPathMap[$theme]);
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

        $themesList = $container->getParameter('ez_core_extra.themes.list');
        $container->setParameter('ez_core_extra.themes.list', array_unique(
            array_merge($themesList, array_keys($themesPathMap)))
        );
        $container->setParameter('ez_core_extra.themes.path_map', $themesPathMap);

        // Override Twig environment
        $isTwigLegacy = version_compare(\Twig_Environment::VERSION, '2.0.0', '<');
        $twigDef = $container->findDefinition('twig');
        $twigDef->addMethodCall('setTemplateNameResolver', [new Reference('ez_core_extra.template_name_resolver')]);
        $twigDef->addMethodCall('setKernelRootDir', [$container->getParameter('kernel.root_dir')]);
        // Different base class for Twig environment depending if legacy is present/activated or not
        if ($container->hasParameter('ezpublish_legacy.enabled') && $container->getParameter('ezpublish_legacy.enabled')) {
            if ($isTwigLegacy) {
                $twigDef->setClass('Lolautruche\EzCoreExtraBundle\Templating\Twig\LegacyBasedTwigLegacyEnvironment');
            } else {
                $twigDef->setClass('Lolautruche\EzCoreExtraBundle\Templating\Twig\LegacyBasedTwigEnvironment');
            }
        } else {
            if ($isTwigLegacy) {
                $twigDef->setClass('Lolautruche\EzCoreExtraBundle\Templating\Twig\TwigLegacyEnvironment');
            } else {
                $twigDef->setClass('Lolautruche\EzCoreExtraBundle\Templating\Twig\TwigEnvironment');
            }
        }
    }
}
