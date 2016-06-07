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

use Lolautruche\EzCoreExtraBundle\Templating\TwigGlobalsExtension;
use PHPUnit_Framework_TestCase;

class TwigGlobalsExtensionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructNoGlobals()
    {
        $extension = new TwigGlobalsExtension();
        self::assertSame('ez_core_extra.globals', $extension->getName());
        self::assertSame([], $extension->getGlobals());
    }

    public function testConstruct()
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

    public function testGlobalsInjection()
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

    public function testGlobalsInjectionNull()
    {
        $extension = new TwigGlobalsExtension();
        $extension->setContextAwareGlobals(null);
        self::assertSame([], $extension->getGlobals());
    }
}
