<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParser;
use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Lolautruche\EzCoreExtraBundle\EventListener\ViewTemplateListener;
use PHPUnit_Framework_TestCase;

class ViewTemplateListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface
     */
    private $dynamicSettingParser;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->createMock('\eZ\Publish\Core\MVC\ConfigResolverInterface');
        $this->dynamicSettingParser = new DynamicSettingParser();
    }

    public function testGetSubscribedEvents()
    {
        self::assertSame(
            [
                MVCEvents::PRE_CONTENT_VIEW => ['onPreContentView', 15],
            ],
            ViewTemplateListener::getSubscribedEvents()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\Symfony\View\View|\eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface
     */
    private function generateView()
    {
        // \eZ\Publish\Core\MVC\Symfony\View\View is only defined in kernel >=6.0
        if (interface_exists('\eZ\Publish\Core\MVC\Symfony\View\View')) {
            $view = $this->createMock('\eZ\Publish\Core\MVC\Symfony\View\View');
        } else {
            $view = $this->createMock('\eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface');
        }

        return $view;
    }

    public function testOnPreViewContentNoParams()
    {
        $view = $this->generateView();
        $event = new PreContentViewEvent($view);
        $view
            ->expects($this->once())
            ->method('getConfigHash')
            ->willReturn([]);
        $view
            ->expects($this->never())
            ->method('addParameters');

        $listener = new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser);
        $listener->onPreContentView($event);
    }

    public function testOnPreViewContentParamsNotArray()
    {
        $view = $this->generateView();
        $event = new PreContentViewEvent($view);
        $view
            ->expects($this->once())
            ->method('getConfigHash')
            ->willReturn(['params' => 'foo']);
        $view
            ->expects($this->never())
            ->method('addParameters');

        (new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser))->onPreContentView($event);
    }

    public function testOnPreViewContentDynamicSettings()
    {
        $view = $this->generateView();
        $event = new PreContentViewEvent($view);

        $configHash = [
            'params' => [
                'foo' => 'bar',
                'dynamic' => '$dynamic_setting$',
                'some' => 'thing',
                'dynamic2' => '$foo;bar;baz$',
            ],
        ];

        $view
            ->expects($this->once())
            ->method('getConfigHash')
            ->willReturn($configHash);

        $dynamicValue1 = 'some_value';
        $dynamicValue2 = ['another' => 'value'];
        $this->configResolver
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->willReturnMap([
                ['dynamic_setting', null, null, $dynamicValue1],
                ['foo', 'bar', 'baz', $dynamicValue2],
            ]);

        $expectedParams = [
                'dynamic' => $dynamicValue1,
                'dynamic2' => $dynamicValue2,
            ] + $configHash['params'];
        $view
            ->expects($this->once())
            ->method('addParameters')
            ->with($expectedParams);

        (new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser))->onPreContentView($event);
    }

    /**
     * @expectedException \Lolautruche\EzCoreExtraBundle\Exception\MissingParameterProviderException
     */
    public function testOnPreViewContentMissingParameterProvider()
    {
        $view = $this->generateView();
        $event = new PreContentViewEvent($view);

        $view
            ->expects($this->once())
            ->method('getConfigHash')
            ->willReturn([
                'params' => [
                    'foo' => ['provider' => 'some_missing_provider'],
                ],
            ]);

        (new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser))->onPreContentView($event);
    }

    public function testOnPreViewContentParameterProvider()
    {
        $view = $this->generateView();
        $event = new PreContentViewEvent($view);

        $providerAlias = 'some_provider';
        $provider = $this->createMock('\Lolautruche\EzCoreExtraBundle\Templating\ViewParameterProviderInterface');
        $listener = new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser);
        $listener->addParameterProvider($provider, $providerAlias);

        $template = 'foo.html.twig';
        $existingParameters = ['location' => new Location(), 'content' => new Content()];
        $view
            ->expects($this->once())
            ->method('getConfigHash')
            ->willReturn([
                'params' => [
                    'foo' => ['provider' => $providerAlias],
                ],
            ]);
        $view
            ->expects($this->once())
            ->method('getTemplateIdentifier')
            ->willReturn($template);
        $view
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn($existingParameters);

        $providedParameters = [
            'some' => 'thing',
            'some_bool' => true,
            'some_array' => ['foo' => 'bar'],
        ];
        $provider
            ->expects($this->once())
            ->method('getParameters')
            ->with(['template' => $template, 'parameters' => $existingParameters])
            ->willReturn($providedParameters);

        $view
            ->expects($this->once())
            ->method('addParameters')
            ->with(['foo' => (object) $providedParameters]);

        $listener->onPreContentView($event);
    }
}
