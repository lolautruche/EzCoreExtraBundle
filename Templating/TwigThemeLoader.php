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
    /**
     * @var TemplateNameResolverInterface
     */
    private $nameResolver;
    
    /**
     * @var Twig_LoaderInterface|Twig_ExistsLoaderInterface|\Twig_Loader_Filesystem
     */
    private $innerFilesystemLoader;

    public function __construct(TemplateNameResolverInterface $templateNameResolver, Twig_LoaderInterface $innerFilesystemLoader)
    {
        $this->innerFilesystemLoader = $innerFilesystemLoader;
        $this->nameResolver = $templateNameResolver;
    }

    public function exists($name)
    {
        return $this->innerFilesystemLoader->exists($this->nameResolver->resolveTemplateName($name));
    }

    public function getSource($name)
    {
        return $this->innerFilesystemLoader->getSource($this->nameResolver->resolveTemplateName($name));
    }

    public function getCacheKey($name)
    {
        return $this->innerFilesystemLoader->getCacheKey($this->nameResolver->resolveTemplateName($name));
    }

    public function isFresh($name, $time)
    {
        return $this->innerFilesystemLoader->isFresh($this->nameResolver->resolveTemplateName($name), $time);
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
