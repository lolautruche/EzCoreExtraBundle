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

use Lolautruche\EzCoreExtraBundle\Templating\ThemeTemplateNameResolver;
use PHPUnit_Framework_TestCase;

class ThemeTemplateNameResolverTest extends PHPUnit_Framework_TestCase
{
    public function templateNameProvider()
    {
        return [
            [null, 'foo.html.twig', 'foo.html.twig'],
            ['my_design', '@ezdesign/foo.html.twig', '@my_design/foo.html.twig'],
            ['my_design', '@AcmeTest/foo.html.twig', '@AcmeTest/foo.html.twig'],
        ];
    }

    /**
     * @dataProvider templateNameProvider
     */
    public function testResolveTemplateName($currentDesign, $templateName, $expectedTemplateName)
    {
        $resolver = new ThemeTemplateNameResolver($currentDesign);
        self::assertSame($expectedTemplateName, $resolver->resolveTemplateName($templateName));
    }

    public function isTemplateDesignNamespacedProvider()
    {
        return [
            [null, 'foo.html.twig', false],
            ['my_design', '@ezdesign/foo.html.twig', true],
            ['my_design', '@my_design/foo.html.twig', true],
            ['my_design', '@AcmeTest/foo.html.twig', false],
        ];
    }

    /**
     * @dataProvider isTemplateDesignNamespacedProvider
     */
    public function testIsTemplateDesignNamespaced($currentDesign, $templateName, $expected)
    {
        $resolver = new ThemeTemplateNameResolver($currentDesign);
        self::assertSame($expected, $resolver->isTemplateDesignNamespaced($templateName));
    }
}
