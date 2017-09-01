<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\View;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

class ExpressionLanguage extends BaseExpressionLanguage
{
    public function __construct($cache = null, array $providers = array())
    {
        // prepend the default provider to let users override it easily
        array_unshift($providers, new ExpressionLanguageProvider());

        parent::__construct($cache, $providers);
    }
}
