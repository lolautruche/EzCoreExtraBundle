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

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

class ThemeAwareTwigEngine extends TwigEngine
{
    const EZ_THEME_NAMESPACE = 'ezdesign';

    /**
     * @var EngineInterface|\Symfony\Component\Templating\StreamingEngineInterface
     */
    private $innerEngine;

    /**
     * Collection of already resolved template names.
     *
     * @var array
     */
    private $resolvedTemplateNames = [];

    /**
     * Design configured for current context (i.e. SiteAccess).
     *
     * @var string
     */
    private $currentDesign;

    public function __construct(EngineInterface $innerEngine)
    {
        $this->innerEngine = $innerEngine;
    }

    public function setCurrentDesign($currentDesign = null)
    {
        $this->currentDesign = $currentDesign;
    }

    /**
     * Replaces generic @ezdesign Twig namespace with current design, configured for current context.
     *
     * @param string $templateName Name of requested template.
     *
     * @return string
     */
    private function resolveName($templateName)
    {
        if (!(isset($this->currentDesign) && ($themeNamespacePos = strpos($templateName, '@'.self::EZ_THEME_NAMESPACE)) === 0)) {
            return $templateName;
        }

        if (!isset($this->resolvedTemplateNames[$templateName])) {
            $this->resolvedTemplateNames[$templateName] = str_replace(
                '@'.self::EZ_THEME_NAMESPACE,
                '@'.$this->currentDesign,
                $templateName
            );
        }

        return $this->resolvedTemplateNames[$templateName];
    }

    public function render($name, array $parameters = array())
    {
        return $this->innerEngine->render($this->resolveName($name), $parameters);
    }

    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        return $this->innerEngine->renderResponse($this->resolveName($view), $parameters, $response);
    }

    public function stream($name, array $parameters = array())
    {
        $this->innerEngine->stream($this->resolveName($name), $parameters);
    }

    public function exists($name)
    {
        return $this->innerEngine->exists($this->resolveName($name));
    }

    public function supports($name)
    {
        return $this->innerEngine->supports($this->resolveName($name));
    }
}
