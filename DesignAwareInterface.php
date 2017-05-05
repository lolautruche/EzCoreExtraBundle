<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle;

interface DesignAwareInterface
{
    /**
     * Injects current design.
     *
     * @param string $currentDesign
     */
    public function setCurrentDesign($currentDesign);
}
