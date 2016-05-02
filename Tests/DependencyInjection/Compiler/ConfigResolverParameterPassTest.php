<?php
namespace Lolautruche\EzCoreExtraBundle\Tests\DependencyInjection\Compiler;

use Lolautruche\EzCoreExtraBundle\DependencyInjection\Compiler\ConfigResolverParameterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ConfigResolverParameterPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $def1Arg1 = 'foo';
        $def1Arg2 = new Reference( 'foo.bar' );
        $def1 = new Definition( 'stdClass', array( $def1Arg1, $def1Arg2 ) );
        $def2 = new Definition( 'stdClass', array( '$bar;some_namespace$', array() ) );
        $def3 = new Definition( 'stdClass', array( '$content.default_ttl;ezsettings;ezdemo_site_admin$' ) );
        $container->setDefinitions(
            array(
                'def1' => $def1,
                'def2' => $def2,
                'def3' => $def3
            )
        );

        $configResolverPass = new ConfigResolverParameterPass();
        $configResolverPass->process( $container );

        // Ensure that non concerned services stayed untouched.
        $this->assertSame( $def1Arg1, $def1->getArgument( 0 ) );
        $this->assertSame( $def1Arg2, $def1->getArgument( 1 ) );

        // Check that concerned services arguments have been correctly transformed.
        /** @var Reference $def2arg1 */
        $def2arg1 = $def2->getArgument( 0 );
        $this->assertInstanceOf( 'Symfony\\Component\\DependencyInjection\\Reference', $def2arg1 );
        $expectedServiceHelperId1 = 'ezpublish_core_extra.config_resolver.bar_some_namespace';
        $this->assertSame( (string)$def2arg1, $expectedServiceHelperId1 );
        $this->assertTrue( $container->has( $expectedServiceHelperId1 ) );
        $defHelper1 = $container->getDefinition( $expectedServiceHelperId1 );
        $this->assertSame( 'ezpublish.config.resolver', $defHelper1->getFactoryService() );
        $this->assertSame( 'getParameter', $defHelper1->getFactoryMethod() );
        $this->assertSame(
            array( 'bar', 'some_namespace' ),
            $defHelper1->getArguments()
        );
        // Also check 2nd argument
        $this->assertSame( array(), $def2->getArgument( 1 ) );

        /** @var Reference $def3arg1 */
        $def3arg1 = $def3->getArgument( 0 );
        $this->assertInstanceOf( 'Symfony\\Component\\DependencyInjection\\Reference', $def3arg1 );
        $expectedServiceHelperId2 = 'ezpublish_core_extra.config_resolver.content.default_ttl_ezsettings_ezdemo_site_admin';
        $this->assertSame( (string)$def3arg1, $expectedServiceHelperId2 );
        $this->assertTrue( $container->has( $expectedServiceHelperId2 ) );
        $defHelper2 = $container->getDefinition( $expectedServiceHelperId2 );
        $this->assertSame( 'ezpublish.config.resolver', $defHelper2->getFactoryService() );
        $this->assertSame( 'getParameter', $defHelper2->getFactoryMethod() );
        $this->assertSame(
            array( 'content.default_ttl', 'ezsettings', 'ezdemo_site_admin' ),
            $defHelper2->getArguments()
        );
    }

    /**
     * @expectedException LogicException
     */
    public function testProcessTooManySegments()
    {
        $container = new ContainerBuilder();
        $container->setDefinition(
            'some_service_def',
            new Definition( 'stdClass', array( '$foo;bar;baz;truc$' ) )
        );

        $configResolverPass = new ConfigResolverParameterPass();
        $configResolverPass->process( $container );
    }
}
