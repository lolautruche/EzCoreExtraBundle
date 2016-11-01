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

use Lolautruche\EzCoreExtraBundle\Templating\TemplateNameResolverInterface;
use Lolautruche\EzCoreExtraBundle\Templating\Twig\DebugTemplate;
use Lolautruche\EzCoreExtraBundle\Templating\TwigEnvironmentTrait;
use PHPUnit_Framework_TestCase;

class TwigEnvironmentTest extends PHPUnit_Framework_TestCase
{
    public function testAddPathMappingNoDebug()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->createMock(TemplateNameResolverInterface::class);
        $rootDir = '/foo/bar/ezpublish';
        $twig = new TwigEnvironmentStub(false);
        $twig->setKernelRootDir($rootDir);
        $twig->setTemplateNameResolver($resolver);

        $resolver
            ->expects($this->never())
            ->method('isTemplateDesignNamespaced');

        $twig->addPathMapping(new \Twig_Source('foo', 'foo.html.twig'));
    }

    public function testAddPathMappingNoTwigSource()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->createMock(TemplateNameResolverInterface::class);
        $rootDir = '/foo/bar/ezpublish';
        $twig = new TwigEnvironmentStub(false);
        $twig->setKernelRootDir($rootDir);
        $twig->setTemplateNameResolver($resolver);

        $resolver
            ->expects($this->never())
            ->method('isTemplateDesignNamespaced');

        $twig->addPathMapping('foo');
    }

    public function testAddPathMapping()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->createMock(TemplateNameResolverInterface::class);
        $rootDir = '/foo/bar/ezpublish';
        $twig = new TwigEnvironmentStub(true);
        $twig->setKernelRootDir($rootDir);
        $twig->setTemplateNameResolver($resolver);

        $resolver
            ->expects($this->once())
            ->method('isTemplateDesignNamespaced')
            ->willReturn(true);

        $twig->addPathMapping(new \Twig_Source('foo', '@foo/bar.html.twig', '/foo/bar/Resources/views/bar.html.twig'));
        self::assertSame('Resources/views/bar.html.twig', DebugTemplate::getTemplatePath('@foo/bar.html.twig'));
    }

    public function testAddPathMappingNotDesignNamespaced()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->createMock(TemplateNameResolverInterface::class);
        $rootDir = '/foo/bar/ezpublish';
        $twig = new TwigEnvironmentStub(true);
        $twig->setKernelRootDir($rootDir);
        $twig->setTemplateNameResolver($resolver);

        $resolver
            ->expects($this->once())
            ->method('isTemplateDesignNamespaced')
            ->willReturn(false);

        $twig->addPathMapping(new \Twig_Source('bar', '@bar/foo.html.twig', '/foo/bar/Resources/views/foo.html.twig'));
        self::assertNull(DebugTemplate::getTemplatePath('@bar/foo.html.twig'));
    }
}

class TwigEnvironmentStub
{
    use TwigEnvironmentTrait;

    private $debug;

    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }

    public function isDebug()
    {
        return $this->debug;
    }
}
