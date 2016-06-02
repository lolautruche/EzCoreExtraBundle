<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Asset;

use Symfony\Component\Asset\PackageInterface;

class ThemePackage implements PackageInterface
{
    /**
     * @var AssetPathResolverInterface
     */
    private $pathResolver;

    /**
     * @var PackageInterface
     */
    private $innerPackage;

    public function __construct(AssetPathResolverInterface $pathResolver, PackageInterface $innerPackage)
    {
        $this->pathResolver = $pathResolver;
        $this->innerPackage = $innerPackage;
    }

    public function getUrl($path)
    {
        return $this->innerPackage->getUrl($this->pathResolver->resolveAssetPath($path));
    }

    public function getVersion($path)
    {
        return $this->innerPackage->getVersion($this->pathResolver->resolveAssetPath($path));
    }
}
