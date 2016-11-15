<?php
namespace Parishop\Tests;

/**
 * Class ImagesTest
 * @package Parishop\Tests
 * @since   1.0
 */
class ImagesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Parishop\Images
     */
    protected $images;

    public function setUp()
    {
        $slice  = new \PHPixie\Slice();
        $config = new \PHPixie\Config($slice);
        /** @var \PHPixie\Config\Storages\Type\Directory $directory */
        $directory    = $config->directory(__DIR__ . '/assets', 'config');
        $this->images = new \Parishop\Images(__DIR__, 'images', $directory->arraySlice('images'));
    }

    public function testAliases()
    {
        $this->assertEquals([], $this->images->aliases());
    }

    public function testMethods()
    {
        $this->assertEquals(
            [
                'imageRender'       => 'render',
                'imageResize'       => 'resize',
                'imageResizeThumb'  => 'resizeThumb',
                'imageResizeMiddle' => 'resizeMiddle',
                'imageResizeFull'   => 'resizeFull',
                'imagePath'         => 'path',
                'imagePathThumb'    => 'pathThumb',
                'imagePathMiddle'   => 'pathMiddle',
                'imagePathFull'     => 'pathFull',
            ], $this->images->methods()
        );
    }

    public function testName()
    {
        $this->assertEquals('images', $this->images->name());
    }

    public function testPath()
    {
        $this->assertEquals('/images/image.jpg', $this->images->path('image.jpg'));
    }

    public function testPathEmpty()
    {
        $this->assertEquals('/images/noImage.png', $this->images->path());
    }

    public function testPathHeight()
    {
        $this->assertEquals('/images/image-x200.jpg', $this->images->path('image.jpg', null, 200));
    }

    public function testPathWidth()
    {
        $this->assertEquals('/images/image-100x.jpg', $this->images->path('image.jpg', 100));
    }

    public function testPathWidthAndHeight()
    {
        $this->assertEquals('/images/image-100x200.jpg', $this->images->path('image.jpg', 100, 200));
    }

    public function testRender()
    {
        $this->assertEquals('<img src="/images/image-100x100.jpg" title="Image" alt="Image" />', $this->images->render('image.jpg', 100, 100, 'Image'));
    }

    public function testRenderAttributes()
    {
        $this->assertEquals(
            '<img src="/images/dir/image-100x100.jpg" title="Image" alt="Image" width="100" height="100"/>', $this->images->render(
            'dir/image.jpg', 100, 100, 'Image', [
                'width'  => 100,
                'height' => 100,
            ]
        )
        );
    }

    public function testResizeEmpty()
    {
        $this->assertEquals('/images/image-100x200.png', $this->images->resize('image.png', 100, 200));
    }

    public function testResizeFull()
    {
        $this->assertEquals('/images/image-400x400.jpg', $this->images->resizeFull('image.jpg'));
    }

    public function testResizeMiddle()
    {
        $this->assertEquals('/images/image-200x200.jpg', $this->images->resizeMiddle('image.jpg'));
    }

    public function testResizeThumb()
    {
        $this->assertEquals('/images/image-100x100.jpg', $this->images->resizeThumb('image.jpg'));
    }

}
