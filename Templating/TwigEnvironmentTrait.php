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

trait TwigEnvironmentTrait
{
    /**
     * @var TemplateNameResolverInterface
     */
    protected $templateNameResolver;

    /**
     * @var TwigThemeLoader
     */
    protected $themeLoader;

    protected $kernelRootDir;

    public function setTemplateNameResolver(TemplateNameResolverInterface $templateNameResolver)
    {
        $this->templateNameResolver = $templateNameResolver;
    }

    public function setThemeLoader(TwigThemeLoader $themeLoader)
    {
        $this->themeLoader = $themeLoader;
    }

    public function setKernelRootDir($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    protected function resolveTemplateName($name)
    {
        // Only resolve real template path if using debug mode
        if ($this->isDebug() && $this->templateNameResolver->isTemplateDesignNamespaced($name)) {
            return $this->themeLoader->findTemplate(
                $this->templateNameResolver->resolveTemplateName($name),
                false
            );
        }

        return $name;
    }
}
