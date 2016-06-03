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

use Psr\Log\LoggerInterface;

class AssetPathResolver implements AssetPathResolverInterface
{
    /**
     * @var array
     */
    private $designPaths;

    /**
     * @var string
     */
    private $currentDesign;

    /**
     * @var string
     */
    private $webRootDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $resolvedPaths = [];

    public function __construct(array $designPaths, $webRootDir, LoggerInterface $logger = null)
    {
        $this->designPaths = $designPaths;
        $this->webRootDir = $webRootDir;
        $this->logger = $logger;
    }

    /**
     * @param string $currentDesign
     */
    public function setCurrentDesign($currentDesign)
    {
        $this->currentDesign = $currentDesign;
    }

    public function resolveAssetPath($path)
    {
        if (isset($this->resolvedPaths[$path])) {
            return $this->resolvedPaths[$path];
        }

        foreach ($this->designPaths[$this->currentDesign] as $tryPath) {
            if (file_exists($this->webRootDir.'/'.$tryPath.'/'.$path)) {
                return $this->resolvedPaths[$path] = $tryPath.'/'.$path;
            }
        }

        if ($this->logger) {
            $this->logger->warning(
                "Asset '$path' cannot be found in any configured themes.\nTried directories: ".implode(
                    ', ',
                    array_values($this->designPaths)
                )
            );
        }

        return $path;
    }
}
