<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\View;

use Lolautruche\EzCoreExtraBundle\Exception\UnsupportedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Decoration of original view that can be used with view parameter providers.
 * It is basically only possible to add new parameters and access to original view parameters.
 */
class ConfigurableView
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\View|\eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface
     */
    private $innerView;

    private $parameters = [];

    public function __construct($innerView)
    {
        $this->innerView = $innerView;
    }

    public function setTemplateIdentifier($templateIdentifier)
    {
        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    /**
     * Returns the registered template identifier.
     *
     * @return string|\Closure
     */
    public function getTemplateIdentifier()
    {
        return $this->innerView->getTemplateIdentifier();
    }

    /**
     * Sets $parameters that will later be injected to the template/closure.
     * If some parameters were already present, $parameters will replace them.
     *
     * @param array $parameters Hash of parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Adds a hash of parameters to the existing parameters.
     *
     * @param array $parameters
     */
    public function addParameters(array $parameters)
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    /**
     * Returns registered parameters.
     *
     * @return array
     */
    public function getParameters()
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
    public function hasParameter($parameterName)
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
    public function getParameter($parameterName)
    {
        if ($this->hasParameter($parameterName)) {
            return $this->parameters[$parameterName];
        }

        return $this->innerView->getParameter($parameterName);
    }

    public function setConfigHash(array $config)
    {
        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    /**
     * Returns the config hash.
     *
     * @return array|null
     */
    public function getConfigHash()
    {
        return $this->innerView->getConfigHash();
    }

    public function setViewType($viewType)
    {
        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    public function getViewType()
    {
        if (method_exists($this->innerView, 'getViewType')) {
            return $this->innerView->getViewType();
        }

        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    public function setControllerReference(ControllerReference $controllerReference)
    {
        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    public function getControllerReference()
    {
        if (method_exists($this->innerView, 'getControllerReference')) {
            return $this->innerView->getControllerReference();
        }

        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    public function setResponse(Response $response)
    {
        throw new UnsupportedException(__METHOD__.' is not supported');
    }

    public function getResponse()
    {
        if (method_exists($this->innerView, 'getResponse')) {
            return $this->innerView->getResponse();
        }

        throw new UnsupportedException(__METHOD__.' is not supported');
    }
}
