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

use Lolautruche\EzCoreExtraBundle\Asset\ThemePackage;
use PHPUnit_Framework_TestCase;

class ThemePackageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Lolautruche\EzCoreExtraBundle\Asset\AssetPathResolverInterface
     */
    private $assetPathResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Asset\PackageInterface
     */
    private $innerPackage;

    protected function setUp()
    {
        parent::setUp();

        $this->assetPathResolver = $this->createMock('\Lolautruche\EzCoreExtraBundle\Asset\AssetPathResolverInterface');
        $this->innerPackage = $this->createMock('\Symfony\Component\Asset\PackageInterface');
    }

    public function testGetUrl()
    {
        $assetPath = 'images/foo.png';
        $fullAssetPath = 'assets/'.$assetPath;
        $currentDesign = 'foo';

        $this->assetPathResolver
            ->expects($this->once())
            ->method('resolveAssetPath')
            ->with($assetPath, $currentDesign)
            ->willReturn($fullAssetPath);
        $this->innerPackage
            ->expects($this->once())
            ->method('getUrl')
            ->with($fullAssetPath)
            ->willReturn("/$fullAssetPath");

        $package = new ThemePackage($this->assetPathResolver, $this->innerPackage);
        $package->setCurrentDesign($currentDesign);
        self::assertSame("/$fullAssetPath", $package->getUrl($assetPath));
    }

    public function testGetVersion()
    {
        $assetPath = 'images/foo.png';
        $fullAssetPath = 'assets/'.$assetPath;
        $currentDesign = 'foo';

        $this->assetPathResolver
            ->expects($this->once())
            ->method('resolveAssetPath')
            ->with($assetPath, $currentDesign)
            ->willReturn($fullAssetPath);
        $version = 'v1';
        $this->innerPackage
            ->expects($this->once())
            ->method('getVersion')
            ->with($fullAssetPath)
            ->willReturn($version);

        $package = new ThemePackage($this->assetPathResolver, $this->innerPackage);
        $package->setCurrentDesign($currentDesign);
        self::assertSame($version, $package->getVersion($assetPath));
    }
}
