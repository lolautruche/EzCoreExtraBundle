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

use Lolautruche\EzCoreExtraBundle\Templating\Twig\DebugTemplate;
use PHPUnit_Framework_TestCase;

class DebugTemplateTest extends PHPUnit_Framework_TestCase
{
    public function testGetTemplatePathNoMapping()
    {
        DebugTemplate::addPathMapping('foo.html.twig', '/foo/bar/foo.html.twig');
        self::assertNull(DebugTemplate::getTemplatePath('bar.html.twig'));
    }

    public function testAddTemplatePathMapping()
    {
        $templateName = 'foo.html.twig';
        $path = '/foo/bar/foo.html.twig';
        DebugTemplate::addPathMapping($templateName, $path);
        self::assertSame($path, DebugTemplate::getTemplatePath($templateName));
    }
}
