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

use Lolautruche\EzCoreExtraBundle\Asset\AssetPathResolver;
use PHPUnit_Framework_TestCase;
use org\bovigo\vfs\vfsStream;

class AssetPathResolverTest extends PHPUnit_Framework_TestCase
{
    public function testResolveAssetPathFail()
    {
        $logger = $this->createMock('\Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('warning');

        $resolver = new AssetPathResolver(['foo' => []], __DIR__, $logger);
        $assetPath = 'images/foo.png';
        self::assertSame($assetPath, $resolver->resolveAssetPath($assetPath, 'foo'));
    }

    /**
     * @expectedException \Lolautruche\EzCoreExtraBundle\Exception\InvalidDesignException
     */
    public function testResolveInvalidDesign()
    {
        $resolver = new AssetPathResolver([], __DIR__);
        $assetPath = 'images/foo.png';
        self::assertSame($assetPath, $resolver->resolveAssetPath($assetPath, 'foo'));
    }

    public function resolveAssetPathProvider()
    {
        return [
            [
                [
                    'foo' => [
                        'themes/theme1',
                        'themes/theme2',
                        'themes/theme3',
                    ],
                ],
                ['themes/theme2', 'themes/theme3'],
                'images/foo.png',
                'themes/theme2/images/foo.png',
            ],
            [
                [
                    'foo' => [
                        'themes/theme1',
                        'themes/theme2',
                        'themes/theme3',
                    ],
                ],
                ['themes/theme2'],
                'images/foo.png',
                'themes/theme2/images/foo.png',
            ],
            [
                [
                    'foo' => [
                        'themes/theme1',
                        'themes/theme2',
                        'themes/theme3',
                    ],
                ],
                ['themes/theme1', 'themes/theme2', 'themes/theme3'],
                'images/foo.png',
                'themes/theme1/images/foo.png',
            ],
            [
                [
                    'foo' => [
                        'themes/theme1',
                        'themes/theme2',
                        'themes/theme3',
                    ],
                ],
                ['themes/theme3'],
                'images/foo.png',
                'themes/theme3/images/foo.png',
            ],
            [
                [
                    'foo' => [
                        'themes/theme1',
                        'themes/theme2',
                        'themes/theme3',
                    ],
                ],
                [],
                'images/foo.png',
                'images/foo.png',
            ],
        ];
    }

    /**
     * @dataProvider resolveAssetPathProvider
     */
    public function testResolveAssetPath(array $designPaths, array $existingPaths, $path, $resolvedPath)
    {
        $webrootDir = vfsStream::setup('web');
        foreach ($designPaths['foo'] as $designPath) {
            if (in_array($designPath, $existingPaths)) {
                $fileInfo = new \SplFileInfo($designPath.'/'.$path);
                $parent = $webrootDir;
                foreach (explode('/', $fileInfo->getPath()) as $dir) {
                    if (!$parent->hasChild($dir)) {
                        $directory = vfsStream::newDirectory($dir)->at($parent);
                    } else {
                        $directory = $parent->getChild($dir);
                    }

                    $parent = $directory;
                }

                vfsStream::newFile($fileInfo->getFilename())->at($parent)->setContent('Vive le sucre !!!');
            }
        }

        $resolver = new AssetPathResolver($designPaths, $webrootDir->url());
        self::assertSame($resolvedPath, $resolver->resolveAssetPath($path, 'foo'));
    }
}
