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

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Twig extension exposing global variables depending on current SiteAccess.
 */
class TwigGlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private ConfigResolverInterface $configResolver,
    ) {}

    public function getGlobals(): array
    {
        return $this->configResolver->getParameter('twig_globals', 'ez_core_extra');
    }
}
