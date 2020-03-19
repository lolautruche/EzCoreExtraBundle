<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Tests\View;

use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Lolautruche\EzCoreExtraBundle\View\ConfigurableView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class ConfigurableViewTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $innerView;

    protected function setUp(): void
    {
        parent::setUp();

        $this->innerView = $this->createMock(View::class);
    }

    /**
     * @expectedException \Lolautruche\EzCoreExtraBundle\Exception\UnsupportedException
     */
    public function testSetTemplateIdentifier(): void
    {
        $view = new ConfigurableView($this->innerView);
        $view->setTemplateIdentifier('foo.html.twig');
    }

    public function testGetTemplateIdentifier(): void
    {
        $this->innerView
            ->expects($this->once())
            ->method('getTemplateIdentifier')
            ->willReturn('foo.html.twig');
        $view = new ConfigurableView($this->innerView);
        $this->assertSame('foo.html.twig', $view->getTemplateIdentifier());
    }

    public function testSetParameters(): void
    {
        $this->innerView
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([]);
        $view = new ConfigurableView($this->innerView);
        $parameters = ['foo' => 'bar'];
        $view->setParameters($parameters);
        $this->assertSame($parameters, $view->getParameters());
        $this->assertSame($parameters['foo'], $view->getParameter('foo'));
    }

    public function testAddParameters(): void
    {
        $this->innerView
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn(['phoenix' => 'rises']);
        $view = new ConfigurableView($this->innerView);
        $view->setParameters(['foo' => 'bar', 'biz' => 'buzz']);
        $view->addParameters(['some' => 'thing', 'foo' => 'haha']);
        $this->assertSame(
            [
                'foo' => 'haha',
                'biz' => 'buzz',
                'some' => 'thing',
                'phoenix' => 'rises',
            ],
            $view->getParameters()
        );
    }

    public function testHasParameterInnerView(): void
    {
        $this->innerView
            ->expects($this->once())
            ->method('hasParameter')
            ->with('foo')
            ->willReturn(true);
        $view = new ConfigurableView($this->innerView);
        $this->assertTrue($view->hasParameter('foo'));
    }

    public function testGetParameterInnerView(): void
    {
        $parameterValue = 'bar';
        $this->innerView
            ->expects($this->once())
            ->method('getParameter')
            ->with('foo')
            ->willReturn($parameterValue);
        $view = new ConfigurableView($this->innerView);
        $this->assertSame($parameterValue, $view->getParameter('foo'));
    }

    public function testHasParameter(): void
    {
        $view = new ConfigurableView($this->innerView);
        $this->assertFalse($view->hasParameter('foo'));
        $view->setParameters(['foo' => 'bar']);
        $this->assertTrue($view->hasParameter('foo'));
    }

    /**
     * @expectedException \Lolautruche\EzCoreExtraBundle\Exception\UnsupportedException
     */
    public function testSetConfigHash(): void
    {
        $view = new ConfigurableView($this->innerView);
        $view->setConfigHash([]);
    }

    public function testGetConfigHash(): void
    {
        $configHash = ['template' => 'foo.html.twig'];
        $this->innerView
            ->expects($this->once())
            ->method('getConfigHash')
            ->willReturn($configHash);

        $view = new ConfigurableView($this->innerView);
        $this->assertSame($configHash, $view->getConfigHash());
    }

    /**
     * @expectedException \Lolautruche\EzCoreExtraBundle\Exception\UnsupportedException
     */
    public function testSetControllerReference(): void
    {
        $view = new ConfigurableView($this->innerView);
        $view->setControllerReference(new ControllerReference('foo'));
    }

    public function testGetControllerReference(): void
    {
        $view = new ConfigurableView($this->innerView);
        $controllerReference = new ControllerReference('foo');
        $this->innerView
            ->expects($this->once())
            ->method('getControllerReference')
            ->willReturn($controllerReference);
        $this->assertSame($controllerReference, $view->getControllerReference());
    }

    /**
     * @expectedException \Lolautruche\EzCoreExtraBundle\Exception\UnsupportedException
     */
    public function testSetViewType(): void
    {
        $view = new ConfigurableView($this->innerView);
        $view->setViewType('foo');
    }

    public function testGetViewType(): void
    {
        $view = new ConfigurableView($this->innerView);
        $this->innerView
            ->expects($this->once())
            ->method('getViewType')
            ->willReturn('foo');
        $this->assertSame('foo', $view->getViewType());
    }

    /**
     * @expectedException \Lolautruche\EzCoreExtraBundle\Exception\UnsupportedException
     */
    public function testSetResponse(): void
    {
        $view = new ConfigurableView($this->innerView);
        $view->setResponse(new Response());
    }

    public function testGetResponse(): void
    {
        $view = new ConfigurableView($this->innerView);
        $response = new Response();
        $this->innerView
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);
        $this->assertSame($response, $view->getResponse());
    }

    public function testGetContent(): void
    {
        $content = new Content();
        $innerView = new ContentView();
        $innerView->setContent($content);
        $view = new ConfigurableView($innerView);
        $this->assertSame($content, $view->getContent());
    }

    public function testGetLocation(): void
    {
        $location = new Location();
        $innerView = new ContentView();
        $innerView->setLocation($location);
        $view = new ConfigurableView($innerView);
        $this->assertSame($location, $view->getLocation());
    }
}
