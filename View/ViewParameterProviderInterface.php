<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\View;

use Lolautruche\EzCoreExtraBundle\Templating\ViewParameterProviderInterface as LegacyParamProviderInterface;

/**
 * Interface for services providing parameters to a view.
 */
interface ViewParameterProviderInterface extends LegacyParamProviderInterface
{
    /**
     * Returns a hash of parameters to inject into the matched view.
     * Key is the parameter name, value is the parameter value.
     *
     * Available view parameters (e.g. "content", "location"...) are accessible from $view.
     *
     * @param ConfigurableView $view Decorated matched view, containing initial parameters.
     * @param array $options
     *
     * @return array
     */
    public function getViewParameters(ConfigurableView $view, array $options = []);
}
