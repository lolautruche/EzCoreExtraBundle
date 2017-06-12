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
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Lolautruche\EzCoreExtraBundle\EventListener\ViewTemplateListener;
use Lolautruche\EzCoreExtraBundle\View\ConfigurableView;
use Lolautruche\EzCoreExtraBundle\View\ViewParameterProviderInterface;
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
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
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
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\Symfony\View\View
     */
    private function generateView()
    {
        return $this->createMock(View::class);
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

        $existingParams = ['content' => new Content()];
        $view
            ->expects($this->any())
            ->method('getParameters')
            ->willReturn($existingParams);
        $expectedParams = [
            'dynamic' => $dynamicValue1,
            'dynamic2' => $dynamicValue2,
        ] + $configHash['params'];
        $view
            ->expects($this->once())
            ->method('setParameters')
            ->with($expectedParams + $existingParams);

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
        $provider = $this->createMock(ViewParameterProviderInterface::class);
        $listener = new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser);
        $listener->addParameterProvider($provider, $providerAlias);

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
            ->expects($this->any())
            ->method('getParameters')
            ->willReturn($existingParameters);

        $providedParameters = [
            'some' => 'thing',
            'some_bool' => true,
            'some_array' => ['foo' => 'bar'],
        ];
        $configurableView = new ConfigurableView($view);
        $provider
            ->expects($this->once())
            ->method('getViewParameters')
            ->with($configurableView, [])
            ->willReturn($providedParameters);

        $view
            ->expects($this->once())
            ->method('setParameters')
            ->with(['foo' => (object) $providedParameters] + $existingParameters);

        $listener->onPreContentView($event);
    }

    public function testOnPreViewContentParameterProviderWithOptions()
    {
        $view = $this->generateView();
        $event = new PreContentViewEvent($view);

        $providerAlias = 'some_provider';
        $provider = $this->createMock(ViewParameterProviderInterface::class);
        $listener = new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser);
        $listener->addParameterProvider($provider, $providerAlias);

        $existingParameters = ['location' => new Location(), 'content' => new Content()];
        $paramProviderOptions = ['foo' => 'bar', 'bool' => true, 'integer' => 123];
        $view
            ->expects($this->once())
            ->method('getConfigHash')
            ->willReturn([
                'params' => [
                    'foo' => ['provider' => $providerAlias, 'options' => $paramProviderOptions],
                ],
            ]);
        $view
            ->expects($this->any())
            ->method('getParameters')
            ->willReturn($existingParameters);

        $providedParameters = [
            'some' => 'thing',
            'some_bool' => true,
            'some_array' => ['foo' => 'bar'],
        ];
        $configurableView = new ConfigurableView($view);
        $provider
            ->expects($this->once())
            ->method('getViewParameters')
            ->with($configurableView, $paramProviderOptions)
            ->willReturn($providedParameters);

        $view
            ->expects($this->once())
            ->method('setParameters')
            ->with(['foo' => (object) $providedParameters] + $existingParameters);

        $listener->onPreContentView($event);
    }

    public function testOnPreViewContentParameterProviderWithDynamicOptions()
    {
        $view = $this->generateView();
        $event = new PreContentViewEvent($view);

        $providerAlias = 'some_provider';
        $provider = $this->createMock(ViewParameterProviderInterface::class);
        $listener = new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser);
        $listener->addParameterProvider($provider, $providerAlias);

        $existingParameters = ['location' => new Location(), 'content' => new Content()];
        $paramProviderOptions = ['foo' => '$foo;ezcoreextra;some_scope$', 'bool' => true, 'integer' => 123];
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('foo', 'ezcoreextra', 'some_scope')
            ->willReturn('bar');
        $view
            ->expects($this->once())
            ->method('getConfigHash')
            ->willReturn([
                'params' => [
                    'foo' => ['provider' => $providerAlias, 'options' => $paramProviderOptions],
                ],
            ]);
        $view
            ->expects($this->any())
            ->method('getParameters')
            ->willReturn($existingParameters);

        $providedParameters = [
            'some' => 'thing',
            'some_bool' => true,
            'some_array' => ['foo' => 'bar'],
        ];

        $configurableView = new ConfigurableView($view);
        $provider
            ->expects($this->once())
            ->method('getViewParameters')
            ->with($configurableView, ['foo' => 'bar'] + $paramProviderOptions)
            ->willReturn($providedParameters);

        $view
            ->expects($this->once())
            ->method('setParameters')
            ->with(['foo' => (object) $providedParameters] + $existingParameters);

        $listener->onPreContentView($event);
    }
}
