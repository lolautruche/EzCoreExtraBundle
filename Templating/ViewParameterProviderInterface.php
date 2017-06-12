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

/**
 * Interface for services that provides parameters to the view.
 *
 * @deprecated Since v1.1. Use \Lolautruche\EzCoreExtraBundle\View\ViewParameterProviderInterface instead.
 */
interface ViewParameterProviderInterface
{
    /**
     * Returns a hash of parameters to inject into the matched view.
     * Key is the parameter name, value is the parameter value.
     *
     * @param array $viewConfig Current view configuration hash.
     *                          Available keys:
     *                          - template: Template used for the view.
     *                          - parameters: Hash of parameters that will be passed to the template.
     *
     * @return array
     */
    public function getParameters(array $viewConfig);
}
