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

use Lolautruche\EzCoreExtraBundle\Templating\Twig\TwigGlobalsExtension;
use PHPUnit\Framework\TestCase;

class TwigGlobalsExtensionTest extends TestCase
{
    public function testConstructNoGlobals(): void
    {
        $extension = new TwigGlobalsExtension();
        self::assertSame([], $extension->getGlobals());
    }

    public function testConstruct(): void
    {
        $variables = [
            'foo' => 'bar',
            'number' => 123,
            'bool' => true,
            'array' => ['some' => 'thing'],
        ];

        $extension = new TwigGlobalsExtension($variables);
        self::assertSame($variables, $extension->getGlobals());
    }

    public function testGlobalsInjection(): void
    {
        $variables = [
            'foo' => 'bar',
            'number' => 123,
            'bool' => true,
            'array' => ['some' => 'thing'],
        ];

        $extension = new TwigGlobalsExtension();
        $extension->setContextAwareGlobals($variables);
        self::assertSame($variables, $extension->getGlobals());
    }

    public function testGlobalsInjectionNull(): void
    {
        $extension = new TwigGlobalsExtension();
        $extension->setContextAwareGlobals(null);
        self::assertSame([], $extension->getGlobals());
    }
}
