<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Tests\View;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Lolautruche\EzCoreExtraBundle\View\ConfigurableView;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class ConfigurableViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $innerView;

    protected function setUp()
    {
        parent::setUp();

        $this->innerView = $this->createMock(View::class);
    }

    /**
     * @expectedException \Lolautruche\EzCoreExtraBundle\Exception\UnsupportedException
     */
    public function testSetTemplateIdentifier()
    {
        $view = new ConfigurableView($this->innerView);
        $view->setTemplateIdentifier('foo.html.twig');
    }

    public function testGetTemplateIdentifier()
    {
        $this->innerView
            ->expects($this->once())
            ->method('getTemplateIdentifier')
            ->willReturn('foo.html.twig');
        $view = new ConfigurableView($this->innerView);
        $this->assertSame('foo.html.twig', $view->getTemplateIdentifier());
    }

    public function testSetParameters()
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

    public function testAddParameters()
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

    public function testHasParameterInnerView()
    {
        $this->innerView
            ->expects($this->once())
            ->method('hasParameter')
            ->with('foo')
            ->willReturn(true);
        $view = new ConfigurableView($this->innerView);
        $this->assertTrue($view->hasParameter('foo'));
    }

    public function testHasParameter()
    {
        $view = new ConfigurableView($this->innerView);
        $this->assertFalse($view->hasParameter('foo'));
        $view->setParameters(['foo' => 'bar']);
        $this->assertTrue($view->hasParameter('foo'));
    }

    /**
     * @expectedException \Lolautruche\EzCoreExtraBundle\Exception\UnsupportedException
     */
    public function testSetConfigHash()
    {
        $view = new ConfigurableView($this->innerView);
        $view->setConfigHash([]);
    }

    public function testGetConfigHash()
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
    public function testSetControllerReference()
    {
        $view = new ConfigurableView($this->innerView);
        $view->setControllerReference(new ControllerReference('foo'));
    }

    public function testGetControllerReference()
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
    public function testSetViewType()
    {
        $view = new ConfigurableView($this->innerView);
        $view->setViewType('foo');
    }

    public function testGetViewType()
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
    public function testSetResponse()
    {
        $view = new ConfigurableView($this->innerView);
        $view->setResponse(new Response());
    }

    public function testGetResponse()
    {
        $view = new ConfigurableView($this->innerView);
        $response = new Response();
        $this->innerView
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);
        $this->assertSame($response, $view->getResponse());
    }
}
