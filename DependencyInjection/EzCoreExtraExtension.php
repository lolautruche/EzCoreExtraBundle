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

use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use Lolautruche\EzCoreExtraBundle\View\ViewParameterProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class EzCoreExtraExtension extends Extension
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

        if (method_exists($container, 'registerForAutoconfiguration')) {
            $container->registerForAutoconfiguration(ViewParameterProviderInterface::class)
                ->addTag('ez_core_extra.view_parameter_provider');
        }
    }

    private function configureDesigns(array $config, ConfigurationProcessor $processor, ContainerBuilder $container)
    {
        $processor->mapConfigArray('twig_globals', $config);
    }
}
