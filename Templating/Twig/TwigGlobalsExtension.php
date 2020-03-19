<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Templating\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Twig extension exposing global variables depending on current SiteAccess.
 */
class TwigGlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * Hash of configured globals for current SiteAccess.
     *
     * @var array
     */
    private $contextAwareGlobals = [];

    public function __construct(array $contextAwareGlobals = [])
    {
        $this->contextAwareGlobals = $contextAwareGlobals;
    }

    public function setContextAwareGlobals(array $contextAwareGlobals = null)
    {
        $this->contextAwareGlobals = $contextAwareGlobals ?: [];
    }

    public function getGlobals()
    {
        return $this->contextAwareGlobals;
    }

    public function getName()
    {
        return 'ez_core_extra.globals';
    }
}
