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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

class PHPStormPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('ez_core_extra.phpstorm.enabled')) {
            return;
        }

        if (!$container->hasParameter('ez_core_extra.themes.path_map')) {
            return;
        }

        $pathConfig = [];
        $twigConfigPath = realpath($container->getParameter('ez_core_extra.phpstorm.twig_config_path'));
        foreach ($container->getParameter('ez_core_extra.themes.path_map') as $theme => $paths) {
            foreach ($paths as $path) {
                if ($theme !== '_override') {
                    $pathConfig[] = [
                        'namespace' => $theme,
                        'path' => $this->makeTwigPathRelative($path, $twigConfigPath),
                    ];
                }

                $pathConfig[] = [
                    'namespace' => 'ezdesign',
                    'path' => $this->makeTwigPathRelative($path, $twigConfigPath),
                ];
            }
        }

        (new Filesystem())->dumpFile(
            $twigConfigPath.'/ide-twig.json',
            json_encode(['namespaces' => $pathConfig], JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Converts absolute $path to a path relative to ide-twig.json config file.
     *
     * @param string $path Absolute path
     * @param string $configPath Absolute path where ide-twig.json is stored
     * @return string
     */
    private function makeTwigPathRelative($path, $configPath)
    {
        return trim(str_replace($configPath, '', $path), '/');
    }
}
