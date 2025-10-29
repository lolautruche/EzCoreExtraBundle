<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\View;

use Closure;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\View\ContentValueView;
use Ibexa\Core\MVC\Symfony\View\LocationValueView;
use Ibexa\Core\MVC\Symfony\View\View;
use Lolautruche\EzCoreExtraBundle\Exception\UnsupportedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Decoration of original view that can be used with view parameter providers.
 * It is basically only possible to add new parameters and access to original view parameters.
 */
class ConfigurableView implements View, ContentValueView, LocationValueView
{
    /**
     * @var \Ibexa\Core\MVC\Symfony\View\View|ContentValueView|LocationValueView
     */
    private $innerView;

    private $parameters = [];

    public function __construct(View $innerView)
    {
        $this->innerView = $innerView;
    }

    public function setTemplateIdentifier($templateIdentifier): void
    {
        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    /**
     * Returns the registered template identifier.
     */
    public function getTemplateIdentifier(): string|Closure
    {
        return $this->innerView->getTemplateIdentifier();
    }

    /**
     * Sets $parameters that will later be injected to the template/closure.
     * If some parameters were already present, $parameters will replace them.
     *
     * @param array $parameters Hash of parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Adds a hash of parameters to the existing parameters.
     *
     * @param array $parameters
     */
    public function addParameters(array $parameters): void
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    /**
     * Returns registered parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters + $this->innerView->getParameters();
    }

    /**
     * Checks if $parameterName exists.
     *
     * @param string $parameterName
     *
     * @return bool
     */
    public function hasParameter($parameterName): bool
    {
        return isset($this->parameters[$parameterName]) || $this->innerView->hasParameter($parameterName);
    }

    /**
     * Returns parameter value by $parameterName.
     *
     * @param string $parameterName
     *
     * @return mixed
     */
    public function getParameter($parameterName): mixed
    {
        if (isset($this->parameters[$parameterName])) {
            return $this->parameters[$parameterName];
        }

        if ($parameterName === 'content') {
            @trigger_error('Access to current content via getParameter() is deprecated. Use getContent() instead.', E_USER_DEPRECATED);
        } elseif ($parameterName === 'location') {
            @trigger_error('Access to current location via getParameter() is deprecated. Use getLocation() instead.', E_USER_DEPRECATED);
        }

        return $this->innerView->getParameter($parameterName);
    }

    public function setConfigHash(array $config): void
    {
        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    /**
     * Returns the config hash.
     */
    public function getConfigHash(): ?array
    {
        return $this->innerView->getConfigHash();
    }

    public function setViewType($viewType): void
    {
        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    public function getViewType()
    {
        return $this->innerView->getViewType();
    }

    public function setControllerReference(ControllerReference $controllerReference): void
    {
        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    public function getControllerReference(): ControllerReference
    {
        return $this->innerView->getControllerReference();
    }

    public function setResponse(Response $response): void
    {
        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    public function getResponse()
    {
        return $this->innerView->getResponse();
    }

    /**
     * Returns the Content.
     */
    public function getContent(): ?Content
    {
        return $this->innerView instanceof ContentValueView ? $this->innerView->getContent() : null;
    }

    public function getLocation(): ?Location
    {
        return $this->innerView instanceof LocationValueView ? $this->innerView->getLocation() : null;
    }
}
