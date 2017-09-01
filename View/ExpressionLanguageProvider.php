<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\View;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[]
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction('loadLocation', function ($arg) {
                return sprintf('$repository->getLocationService()->loadLocation(%s)', $arg);
            }, function (array $variables, $value) {
                return $variables['repository']->getLocationService()->loadLocation($value);
            }),

            new ExpressionFunction('loadContent', function ($arg) {
                return sprintf('$repository->getContentService()->loadContent(%s)', $arg);
            }, function (array $variables, $value) {
                return $variables['repository']->getContentService()->loadContent($value);
            }),

            new ExpressionFunction('loadContentType', function ($arg) {
                return sprintf('$repository->getContentTypeService()->loadContentType(%s)', $arg);
            }, function (array $variables, $value) {
                return $variables['repository']->getContentTypeService()->loadContentType($value);
            }),
        ];
    }
}
