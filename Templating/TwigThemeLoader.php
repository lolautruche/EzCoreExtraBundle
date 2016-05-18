<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Templating;

use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Twig_ExistsLoaderInterface;
use Twig_LoaderInterface;

/**
 * Proxy to regular Twig FilesystemLoader.
 * It resolves generic @ezdesign namespace to the actual current namespace.
 *
 * @note It extends \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader because methods specific to this loader
 * (e.g. related to paths and namespaces) are not part of an interface.
 */
class TwigThemeLoader extends FilesystemLoader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface
{
    const EZ_THEME_NAMESPACE = 'ezdesign';

    /**
     * @var Twig_LoaderInterface|Twig_ExistsLoaderInterface|\Twig_Loader_Filesystem
     */
    private $innerFilesystemLoader;

    /**
     * Collection of already resolved template names.
     *
     * @var array
     */
    private $resolvedTemplateNames = [];

    /**
     * @var string
     */
    private $currentTheme;

    public function __construct(Twig_LoaderInterface $innerFilesystemLoader)
    {
        $this->innerFilesystemLoader = $innerFilesystemLoader;
    }

    public function setCurrentTheme($currentTheme)
    {
        $this->currentTheme = $currentTheme;
    }

    private function resolveName($name)
    {
        if (strpos($name, '@'.self::EZ_THEME_NAMESPACE) === false) {
            return $name;
        } elseif (isset($this->resolvedTemplateNames[$name])) {
            return $this->resolvedTemplateNames[$name];
        }

        return $this->resolvedTemplateNames[$name] = str_replace('@'.self::EZ_THEME_NAMESPACE, '@'.$this->currentTheme, $name);
    }

    public function exists($name)
    {
        return $this->innerFilesystemLoader->exists($this->resolveName($name));
    }

    public function getSource($name)
    {
        return $this->innerFilesystemLoader->getSource($this->resolveName($name));
    }

    public function getCacheKey($name)
    {
        return $this->innerFilesystemLoader->getCacheKey($this->resolveName($name));
    }

    public function isFresh($name, $time)
    {
        return $this->innerFilesystemLoader->isFresh($this->resolveName($name), $time);
    }

    public function getPaths($namespace = self::MAIN_NAMESPACE)
    {
        return $this->innerFilesystemLoader->getPaths($namespace);
    }

    public function getNamespaces()
    {
        return $this->innerFilesystemLoader->getNamespaces();
    }

    public function setPaths($paths, $namespace = self::MAIN_NAMESPACE)
    {
        $this->innerFilesystemLoader->setPaths($paths, $namespace);
    }

    public function addPath($path, $namespace = self::MAIN_NAMESPACE)
    {
        $this->innerFilesystemLoader->addPath($path, $namespace);
    }

    public function prependPath($path, $namespace = self::MAIN_NAMESPACE)
    {
        $this->innerFilesystemLoader->prependPath($path, $namespace);
    }
}
