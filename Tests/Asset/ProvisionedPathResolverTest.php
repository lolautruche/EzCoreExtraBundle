<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Tests\Asset;

use Lolautruche\EzCoreExtraBundle\Asset\AssetPathResolverInterface;
use Lolautruche\EzCoreExtraBundle\Asset\ProvisionedPathResolver;
use PHPUnit_Framework_TestCase;

class ProvisionedPathResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AssetPathResolverInterface
     */
    private $innerResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->innerResolver = $this->createMock(AssetPathResolverInterface::class);
    }

    public function testResolvePathNotProvisioned()
    {
        $assetLogicalPath = 'images/some_image.jpg';
        $design = 'foo';
        $expected = 'some/path/images/some_image.jpg';
        $this->innerResolver
            ->expects($this->once())
            ->method('resolveAssetPath')
            ->with($assetLogicalPath, $design)
            ->willReturn($expected);

        $resolver = new ProvisionedPathResolver(
            ['bar' => ['images/some_image.jpg' => 'other/path/images/some_image.jpg']],
            $this->innerResolver
        );
        self::assertSame($expected, $resolver->resolveAssetPath($assetLogicalPath, $design));
    }

    public function testResolveProvisionedPath()
    {
        $expected = 'some/path/images/some_image.jpg';
        $assetLogicalPath = 'images/some_image.jpg';
        $resolvedPaths = [
            'foo' => [$assetLogicalPath => $expected]
        ];
        $design = 'foo';

        $resolver = new ProvisionedPathResolver($resolvedPaths, $this->innerResolver);
        self::assertSame($expected, $resolver->resolveAssetPath($assetLogicalPath, $design));
    }
}
