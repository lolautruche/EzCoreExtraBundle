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

use Lolautruche\EzCoreExtraBundle\Templating\ThemeAwareTwigEngine;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;

class ThemeAwareTwigEngineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    private $innerEngine;

    protected function setUp()
    {
        parent::setUp();
        $this->innerEngine = $this->getMock('\Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
    }

    public function templateNameProvider()
    {
        return [
            [null, 'foo.html.twig', 'foo.html.twig'],
            [null, '@ezdesign/foo.html.twig', '@ezdesign/foo.html.twig'],
            ['my_design', '@ezdesign/foo.html.twig', '@my_design/foo.html.twig'],
            ['my_design', '@AcmeTest/foo.html.twig', '@AcmeTest/foo.html.twig'],
        ];
    }

    /**
     * @dataProvider templateNameProvider
     */
    public function testRender($currentDesign, $templateName, $expectedTemplateName)
    {
        $parameters = ['foo' => 'bar'];
        $this->innerEngine
            ->expects($this->once())
            ->method('render')
            ->with($expectedTemplateName, $parameters);

        $engine = new ThemeAwareTwigEngine($this->innerEngine);
        $engine->setCurrentDesign($currentDesign);
        $engine->render($templateName, $parameters);
    }

    /**
     * @dataProvider templateNameProvider
     */
    public function testRenderResponse($currentDesign, $templateName, $expectedTemplateName)
    {
        $parameters = ['foo' => 'bar'];
        $response = new Response();
        $this->innerEngine
            ->expects($this->once())
            ->method('renderResponse')
            ->with($expectedTemplateName, $parameters, $response);

        $engine = new ThemeAwareTwigEngine($this->innerEngine);
        $engine->setCurrentDesign($currentDesign);
        $engine->renderResponse($templateName, $parameters, $response);
    }

    /**
     * @dataProvider templateNameProvider
     */
    public function testExists($currentDesign, $templateName, $expectedTemplateName)
    {
        $this->innerEngine
            ->expects($this->once())
            ->method('exists')
            ->with($expectedTemplateName);

        $engine = new ThemeAwareTwigEngine($this->innerEngine);
        $engine->setCurrentDesign($currentDesign);
        $engine->exists($templateName);
    }

    /**
     * @dataProvider templateNameProvider
     */
    public function testSupports($currentDesign, $templateName, $expectedTemplateName)
    {
        $this->innerEngine
            ->expects($this->once())
            ->method('supports')
            ->with($expectedTemplateName);

        $engine = new ThemeAwareTwigEngine($this->innerEngine);
        $engine->setCurrentDesign($currentDesign);
        $engine->supports($templateName);
    }
}
