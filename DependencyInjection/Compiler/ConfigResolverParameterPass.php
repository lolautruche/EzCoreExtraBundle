<?php
namespace Lolautruche\EzCoreExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will resolve config resolver parameters, delimited by $ chars (e.g. $my_parameter$).
 * It will replace those parameters by fake services having the config resolver as factory.
 * The factory method will then return the right value, at runtime.
 *
 * Supported syntax for parameters: $<paramName>[;<namespace>[;<scope>]]$
 *
 * The following will work :
 * $my_param$ (using default namespace - ezsettings - with current scope).
 * $my_param;foo$ (using "foo" as namespace, in current scope).
 * $my_param;foo;some_siteaccess$ (using "foo" as namespace, forcing "some_siteaccess scope").
 */
class ConfigResolverParameterPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        foreach ( $container->getDefinitions() as $definition )
        {
            $replaceArguments = array();
            foreach ( $definition->getArguments() as $i => $arg )
            {
                if ( is_string( $arg ) && strpos( $arg, '$' ) === 0 && substr( $arg, -1 ) === '$' )
                {
                    $configResolverParams = explode( ';', substr( $arg, 1, -1 ) );
                    if ( count( $configResolverParams ) > 3 )
                    {
                        throw new \LogicException( "Config resolver parameters can't have more than 3 segments: \$paramName;namespace;scope\$" );
                    }

                    $paramConverter = new Definition( 'stdClass', $configResolverParams );
                    $paramConverter
                        ->setFactoryService( 'ezpublish.config.resolver' )
                        ->setFactoryMethod( 'getParameter' );

                    $serviceId = 'ezpublish_core_extra.config_resolver.' . implode( '_', $configResolverParams );
                    $container->setDefinition( $serviceId, $paramConverter );
                    $replaceArguments[$i] = new Reference( $serviceId );
                };
            }

            if ( empty( $replaceArguments ) )
                continue;

            foreach ( $replaceArguments as $i => $arg )
            {
                $definition->replaceArgument( $i, $arg );
            }
        }
    }
}
