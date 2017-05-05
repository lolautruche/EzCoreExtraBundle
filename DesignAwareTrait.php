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

trait DesignAwareTrait
{
    /**
     * @var string
     */
    protected $currentDesign;

    /**
     * Injects the current design.
     *
     * @param string $currentDesign
     */
    public function setCurrentDesign($currentDesign)
    {
        $this->currentDesign = $currentDesign;
    }
}
