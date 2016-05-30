<?php

/**
 * Created by PhpStorm.
 * User: lolautruche
 * Date: 30/05/2016
 * Time: 15:54
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
}
