<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Tests\Templating;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Lolautruche\EzCoreExtraBundle\Templating\Twig\TwigGlobalsExtension;
use PHPUnit\Framework\TestCase;

class TwigGlobalsExtensionTest extends TestCase
{
    public function testConstruct(): void
    {
        $variables = [
            'foo' => 'bar',
            'number' => 123,
            'bool' => true,
            'array' => ['some' => 'thing'],
        ];

        $configResolverMock = $this->createMock(ConfigResolverInterface::class);
        $configResolverMock
            ->expects(self::once())
            ->method('getParameter')
            ->with(
                self::identicalTo('twig_globals'),
                self::identicalTo('ez_core_extra')
            )->willReturn($variables);

        $extension = new TwigGlobalsExtension($configResolverMock);
        self::assertSame($variables, $extension->getGlobals());
    }
}
