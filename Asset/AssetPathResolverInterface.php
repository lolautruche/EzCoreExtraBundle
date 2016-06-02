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

/**
 * Interface for asset path resolvers.
 * An asset path resolver will check provided asset path and resolve it for current design.
 */
interface AssetPathResolverInterface
{
    /**
     * Resolves provided asset path within current design and returns correct asset path.
     *
     * @param string $path asset path to resolve.
     *
     * @return string
     */
    public function resolveAssetPath($path);
}
