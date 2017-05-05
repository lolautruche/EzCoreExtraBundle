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

interface AssetPathProvisionerInterface
{
    /**
     * Pre-resolves assets paths for a given design from themes paths, where are stored physical assets.
     * Returns an map with asset logical path as key and its resolved path (relative to webroot dir) as value.
     * Example => ['images/foo.png' => 'asset/themes/some_theme/images/foo.png']
     *
     * @param array $assetsPaths
     * @param string $design
     * @return array
     */
    public function provisionResolvedPaths(array $assetsPaths, $design);
}
