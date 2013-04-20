<?php

namespace Lolart\EzPublishCoreExtraBundle;

use Lolart\EzPublishCoreExtraBundle\DependencyInjection\Compiler\ConfigResolverParameterPass;
use Lolart\EzPublishCoreExtraBundle\DependencyInjection\EzPublishCoreExtraExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JVEzPublishCoreExtraBundle extends Bundle
{
    protected $name = 'eZPublishCoreExtraBundle';

    public function build( ContainerBuilder $container )
    {
        $container->addCompilerPass( new ConfigResolverParameterPass() );
    }

    public function getContainerExtension()
    {
        return new EzPublishCoreExtraExtension();
    }
}
