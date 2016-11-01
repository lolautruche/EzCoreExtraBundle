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

use Lolautruche\EzCoreExtraBundle\Templating\Twig\DebugTemplate;

trait TwigEnvironmentTrait
{
    /**
     * @var TemplateNameResolverInterface
     */
    protected $templateNameResolver;

    protected $kernelRootDir;

    public function setTemplateNameResolver(TemplateNameResolverInterface $templateNameResolver)
    {
        $this->templateNameResolver = $templateNameResolver;
    }

    public function setKernelRootDir($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    public function addPathMapping($source)
    {
        if (!($this->isDebug() && $source instanceof \Twig_Source)) {
            return;
        }

        if ($this->templateNameResolver->isTemplateDesignNamespaced($source->getName())) {
            DebugTemplate::addPathMapping(
                $source->getName(),
                str_replace(dirname($this->kernelRootDir).'/', '', $source->getPath())
            );
        }
    }
}
