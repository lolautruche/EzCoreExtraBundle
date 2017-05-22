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

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class ConfigurableViewParameterProvider implements ViewParameterProviderInterface
{
    /**
     * @var OptionsResolver
     */
    private $resolver;

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

    final public function getParameters(array $viewConfig, array $options = [])
    {
        return $this->doGetParameters($viewConfig, $this->getResolver()->resolve($options));
    }

    /**
     * Returns the hash of parameters to be injected into the matched view.
     * Key is the parameter name, value is the parameter value.
     *
     * The parameters array is processed with the OptionsResolver, meaning that it has been validated, and contains
     * the default values when applicable.
     *
     * @see getParameters()
     *
     * @param array $viewConfig Current view configuration hash.
     *                          Available keys:
     *                          - template: Template used for the view.
     *                          - parameters: Hash of parameters that will be passed to the template (e.g. 'content', 'location'...)
     * @param array $settings Configured settings for the view parameter provider.
     *
     * @return array
     */
    abstract protected function doGetParameters(array $viewConfig, array $settings = []);

    /**
     * Builds the resolver, and configures it using configureOptions().
     *
     * @return OptionsResolver
     */
    private function getResolver()
    {
        if ($this->resolver === null) {
            $this->resolver = new OptionsResolver();
            $this->configureOptions($this->resolver);
        }

        return $this->resolver;
    }
}
