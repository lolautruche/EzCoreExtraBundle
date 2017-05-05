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

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class EzCoreExtraExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('default_settings.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $processor = new ConfigurationProcessor($container, 'ez_core_extra');

        $this->configureDesigns($config, $processor, $container);
    }

    private function configureDesigns(array $config, ConfigurationProcessor $processor, ContainerBuilder $container)
    {
        // Always add _base theme to the list.
        foreach ($config['design']['list'] as $design => &$themes) {
            $themes[] = '_base';
        }
        $container->setParameter('ez_core_extra.themes.design_list', $config['design']['list']);
        $container->setParameter('ez_core_extra.themes.override_paths', $config['design']['override_paths']);
        $container->setParameter('ez_theme.asset_resolution.disabled', $config['design']['disable_assets_pre_resolution']);

        // PHPStorm settings
        $container->setParameter('ez_core_extra.phpstorm.enabled', $config['phpstorm']['enabled']);
        $container->setParameter('ez_core_extra.phpstorm.twig_config_path', $config['phpstorm']['twig_config_path']);

        // SiteAccess aware settings
        $processor->mapConfig(
            $config,
            function ($scopeSettings, $currentScope, ContextualizerInterface $contextualizer) use ($config) {
                if (isset($scopeSettings['design'])) {
                    if (!isset($config['design']['list'][$scopeSettings['design']])) {
                        throw new InvalidArgumentException(
                            "Selected design for $currentScope '{$scopeSettings['design']}' is invalid. Did you forget to define it?"
                        );
                    }

                    $contextualizer->setContextualParameter('design', $currentScope, $scopeSettings['design']);
                }

                if (isset($scopeSettings['twig_globals'])) {
                    $contextualizer->setContextualParameter('twig_globals', $currentScope, $scopeSettings['twig_globals']);
                }
            }
        );
    }

    public function prepend(ContainerBuilder $container)
    {
        // Override Twig base class when in debug
        if ($container->getParameter('kernel.debug')) {
            $container->prependExtensionConfig('twig', ['base_template_class' => 'Lolautruche\EzCoreExtraBundle\Templating\Twig\DebugTemplate']);
        }
    }
}
