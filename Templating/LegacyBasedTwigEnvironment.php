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

use eZ\Publish\Core\MVC\Legacy\Templating\Twig\Environment as LegacyTwigEnvironment;

class LegacyBasedTwigEnvironment extends LegacyTwigEnvironment
{
    use TwigEnvironmentTrait;

    public function compileSource($source, $name = null)
    {
        return parent::compileSource(
            $source,
            substr($name, -5) === '.twig' ? $this->resolveTemplateName($name) : $name
        );
    }
}
