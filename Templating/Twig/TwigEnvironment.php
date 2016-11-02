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

use Lolautruche\EzCoreExtraBundle\Templating\Twig\TwigEnvironmentTrait;
use Twig_Environment;

class TwigEnvironment extends Twig_Environment
{
    use TwigEnvironmentTrait;

    public function compileSource($source, $name = null)
    {
        $this->addPathMapping($source);

        return parent::compileSource($source, $name);
    }
}
