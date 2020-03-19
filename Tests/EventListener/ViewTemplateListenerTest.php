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
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Lolautruche\EzCoreExtraBundle\EventListener\ViewTemplateListener;
use Lolautruche\EzCoreExtraBundle\Exception\MissingParameterProviderException;
use Lolautruche\EzCoreExtraBundle\View\ConfigurableView;
use Lolautruche\EzCoreExtraBundle\View\ExpressionLanguage;
use Lolautruche\EzCoreExtraBundle\View\ViewParameterProviderInterface;
use PHPUnit\Framework\TestCase;

class ViewTemplateListenerTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface
     */
    private $dynamicSettingParser;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->dynamicSettingParser = new DynamicSettingParser();
        $this->repository = $this->createMock(Repository::class);
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertSame(
            [
                MVCEvents::PRE_CONTENT_VIEW => ['onPreContentView', 15],
            ],
            ViewTemplateListener::getSubscribedEvents()
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    private function generateView()
    {
        $view = $this->createMock(ContentView::class);
        $view
            ->method('getContent')
            ->willReturn(new Content());
        $view
            ->method('getLocation')
            ->willReturn(new Location());
        return $view;
    }

    public function testOnPreViewContentNoParams(): void
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

        $listener = new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser, $this->repository, $this->expressionLanguage);
        $listener->onPreContentView($event);
    }

    public function testOnPreViewContentParamsNotArray(): void
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

        (new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser, $this->repository, $this->expressionLanguage))->onPreContentView($event);
    }

    public function testOnPreViewContentDynamicSettings(): void
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

        (new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser, $this->repository, $this->expressionLanguage))->onPreContentView($event);
    }

    public function testOnPreViewContentMissingParameterProvider(): void
    {
        $this->expectException(MissingParameterProviderException::class);

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

        (new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser, $this->repository, $this->expressionLanguage))->onPreContentView($event);
    }

    public function testOnPreViewContentParameterProvider(): void
    {
        $view = $this->generateView();
        $event = new PreContentViewEvent($view);

        $providerAlias = 'some_provider';
        $provider = $this->createMock(ViewParameterProviderInterface::class);
        $listener = new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser, $this->repository, $this->expressionLanguage);
        $listener->addParameterProvider($provider, $providerAlias);

        $existingParameters = ['existing' => 'parameter'];
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
        $configurableView->addParameters([
            'content' => $view->getContent(),
            'location' => $view->getLocation(),
        ]);
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

    public function testOnPreViewContentParameterProviderWithOptions(): void
    {
        $view = $this->generateView();
        $event = new PreContentViewEvent($view);

        $providerAlias = 'some_provider';
        $provider = $this->createMock(ViewParameterProviderInterface::class);
        $listener = new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser, $this->repository, $this->expressionLanguage);
        $listener->addParameterProvider($provider, $providerAlias);

        $existingParameters = ['existing' => 'parameter'];
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
        $configurableView->addParameters([
            'content' => $view->getContent(),
            'location' => $view->getLocation(),
        ]);
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

    public function testOnPreViewContentParameterProviderWithDynamicOptions(): void
    {
        $view = $this->generateView();
        $event = new PreContentViewEvent($view);

        $providerAlias = 'some_provider';
        $provider = $this->createMock(ViewParameterProviderInterface::class);
        $listener = new ViewTemplateListener($this->configResolver, $this->dynamicSettingParser, $this->repository, $this->expressionLanguage);
        $listener->addParameterProvider($provider, $providerAlias);

        $existingParameters = ['existing' => 'parameter'];
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
        $configurableView->addParameters([
            'content' => $view->getContent(),
            'location' => $view->getLocation(),
        ]);
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
