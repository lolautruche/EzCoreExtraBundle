<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\EventListener;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Lolautruche\EzCoreExtraBundle\Exception\MissingParameterProviderException;
use Lolautruche\EzCoreExtraBundle\Templating\ViewParameterProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener that will inject pre-configured parameters into matched view.
 */
class ViewTemplateListener implements EventSubscriberInterface
{
    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var DynamicSettingParserInterface
     */
    private $settingParser;

    /**
     * @var ViewParameterProviderInterface[]
     */
    private $parameterProviders = [];

    public function __construct(ConfigResolverInterface $configResolver, DynamicSettingParserInterface $settingParser)
    {
        $this->configResolver = $configResolver;
        $this->settingParser = $settingParser;
    }

    public static function getSubscribedEvents()
    {
        return [
            MVCEvents::PRE_CONTENT_VIEW => ['onPreContentView', 15],
        ];
    }

    public function addParameterProvider(ViewParameterProviderInterface $provider, $alias)
    {
        $this->parameterProviders[$alias] = $provider;
    }

    public function onPreContentView(PreContentViewEvent $event)
    {
        /** @var \eZ\Publish\Core\MVC\Symfony\View\View|\eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface $contentView */
        $contentView = $event->getContentView();
        $configHash = $contentView->getConfigHash();
        if (!isset($configHash['params']) || !is_array($configHash['params'])) {
            return;
        }

        foreach ($configHash['params'] as $paramName => &$param) {
            if (is_string($param) && $this->settingParser->isDynamicSetting($param)) {
                $parsed = $this->settingParser->parseDynamicSetting($param);
                $param = $this->configResolver->getParameter($parsed['param'], $parsed['namespace'], $parsed['scope']);
            } elseif (is_array($param) && isset($param['provider'])) {
                if (!isset($this->parameterProviders[$param['provider']])) {
                    throw new MissingParameterProviderException(
                        "ParameterProvider '{$param['provider']}' could not be found. ".
                        "Did you register it as a service with 'ez_core_extra.view_template_listener' tag?"
                    );
                }

                // Use provider to get the array of parameters and switch param value with it.
                // The resulted array is casted to object (stdClass) for convenient use in templates.
                // Parameter name will be unchanged. Parameters returned by provider will then be "namespaced" by the parameter name.
                $param = (object) $this->parameterProviders[$param['provider']]->getParameters([
                    'template' => $contentView->getTemplateIdentifier(),
                    'parameters' => $contentView->getParameters(),
                ]);
            }
        }

        $contentView->setParameters(array_replace($contentView->getParameters(), $configHash['params']));
    }
}
