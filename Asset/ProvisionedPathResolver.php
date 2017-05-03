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

class ProvisionedPathResolver implements AssetPathResolverInterface
{
    /**
     * @var array
     */
    private $resolvedPaths;

    /**
     * @var AssetPathResolverInterface
     */
    private $innerResolver;

    public function __construct(array $resolvedPaths, AssetPathResolverInterface $innerResolver)
    {
        $this->resolvedPaths = $resolvedPaths;
        $this->innerResolver = $innerResolver;
    }

    /**
     * Looks for $path within pre-resolved paths for provided design.
     * If it cannot be found, fallbacks to original resolver.
     *
     * {@inheritdoc}
     */
    public function resolveAssetPath($path, $design)
    {
        if (!isset($this->resolvedPaths[$design][$path])) {
            return $this->innerResolver->resolveAssetPath($path, $design);
        }

        return $this->resolvedPaths[$design][$path];
    }
}
