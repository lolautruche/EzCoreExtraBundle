<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\View;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class ConfigurableViewParameterProvider implements ViewParameterProviderInterface
{
    private OptionsResolver $resolver;

    /**
     * Configures the OptionsResolver for the param provider.
     *
     * Example:
     * ```php
     * // type setting will be required
     * $resolver->setRequired('type');
     * // limit setting will be optional, and will have a default value of 10
     * $resolver->setDefault('limit', 10);
     * ```
     *
     * @param OptionsResolver $optionsResolver
     */
    abstract protected function configureOptions(OptionsResolver $optionsResolver);

    final public function getViewParameters(ConfigurableView $view, array $options = []): array
    {
        return $this->doGetParameters($view, $this->getResolver()->resolve($options));
    }

    /**
     * Returns the hash of parameters to be injected into the matched view.
     * Key is the parameter name, value is the parameter value.
     *
     * The parameters array is processed with the OptionsResolver, meaning that it has been validated, and contains
     * the default values when applicable.
     *
     * Available view parameters (e.g. "content", "location"...) are accessible from $view.
     *
     * @see getParameters()
     *
     * @param ConfigurableView $view Decorated matched view, containing initial parameters.
     * @param array $options Configured settings for the view parameter provider.
     *
     * @return array
     */
    abstract protected function doGetParameters(ConfigurableView $view, array $options = []): array;

    /**
     * Builds the resolver, and configures it using configureOptions().
     *
     * @return OptionsResolver
     */
    private function getResolver(): OptionsResolver
    {
        if ($this->resolver === null) {
            $this->resolver = new OptionsResolver();
            $this->configureOptions($this->resolver);
        }

        return $this->resolver;
    }
}
