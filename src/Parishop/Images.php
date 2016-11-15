<?php
namespace Parishop;

/**
 * Class Images
 * @package Parishop
 * @since   1.0
 */
class Images implements \PHPixie\Template\Extensions\Extension
{
    /**
     * @var string
     * @since 1.0
     */
    protected $webRootDir;

    /**
     * @var string
     * @since 1.0
     */
    protected $imagesDir;

    /**
     * @var \PHPixie\Slice\Type\ArrayData
     * @since 1.0
     */
    protected $config;

    /**
     * @var \PHPixie\Image
     * @since 1.0
     */
    protected $image;

    /**
     * @var string
     * @since 1.0
     */
    protected $noImage;

    /**
     * Images constructor.
     * @param string                        $webRootDir
     * @param string                        $imagesDir
     * @param \PHPixie\Slice\Type\ArrayData $config
     * @param string                        $noImage
     * @since 1.0
     */
    public function __construct($webRootDir, $imagesDir, $config, $noImage = 'noImage.png')
    {
        $this->webRootDir = $webRootDir;
        $this->imagesDir  = $imagesDir;
        $this->config     = $config;
        $this->noImage    = $noImage;
        $this->image      = new \PHPixie\Image($this->config->get('driver'));
    }

    /**
     * @return array
     * @since 1.0
     */
    public function aliases()
    {
        return array();
    }

    /**
     * @return array
     * @since 1.0
     */
    public function methods()
    {
        return array(
            'imageRender'       => 'render',
            'imageResize'       => 'resize',
            'imageResizeThumb'  => 'resizeThumb',
            'imageResizeMiddle' => 'resizeMiddle',
            'imageResizeFull'   => 'resizeFull',
            'imagePath'         => 'path',
            'imagePathThumb'    => 'pathThumb',
            'imagePathMiddle'   => 'pathMiddle',
            'imagePathFull'     => 'pathFull',
        );
    }

    /**
     * @return string
     * @since 1.0
     */
    public function name()
    {
        return 'images';
    }

    /**
     * @param string $imageName
     * @param int    $width
     * @param int    $height
     * @return string
     * @since 1.0
     */
    public function path($imageName = null, $width = null, $height = null)
    {
        $sourceImage = $this->getSource($imageName);

        return $this->getDestination($sourceImage, $width, $height);
    }

    /**
     * @param string $imageName
     * @return string
     * @since 1.0
     */
    public function pathFull($imageName)
    {
        return $this->path($imageName, $this->config->get('full.width'), $this->config->get('full.height'));
    }

    /**
     * @param string $imageName
     * @return string
     * @since 1.0
     */
    public function pathMiddle($imageName)
    {
        return $this->path($imageName, $this->config->get('middle.width'), $this->config->get('middle.height'));
    }

    /**
     * @param string $imageName
     * @return string
     * @since 1.0
     */
    public function pathThumb($imageName)
    {
        return $this->path($imageName, $this->config->get('thumb.width'), $this->config->get('thumb.height'));
    }

    /**
     * @param string $imageName
     * @param int    $width
     * @param int    $height
     * @param string $title
     * @param array  $attributes
     * @param string $format
     * @param int    $quality
     * @return string
     * @since 1.0
     */
    public function render($imageName = null, $width = null, $height = null, $title = null, $attributes = [], $format = null, $quality = 90)
    {
        $destinationImage = $this->resize($imageName, $width, $height, $format, $quality);
        $attributesArray  = [];
        foreach($attributes as $key => $value) {
            $attributesArray[] = $key . '="' . $value . '"';
        }

        return '<img src="' . $destinationImage . '" title="' . $title . '" alt="' . $title . '" ' . implode(' ', $attributesArray) . '/>';
    }

    /**
     * @param string $imageName
     * @param int    $width
     * @param int    $height
     * @param string $format
     * @param int    $quality
     * @return string
     * @throws \PHPixie\Image\Exception
     * @since 1.0
     */
    public function resize($imageName, $width, $height, $format = null, $quality = 90)
    {
        $sourceImage = $this->getSource($imageName);

        $destinationImage = $this->getDestination($sourceImage, $width, $height);

        return $this->go($sourceImage, $destinationImage, $width, $height, $format, $quality);
    }

    /**
     * @param string $imageName
     * @param string $format
     * @param int    $quality
     * @return mixed
     * @since 1.0
     */
    public function resizeFull($imageName, $format = null, $quality = 90)
    {
        $sourceImage      = $this->getSource($imageName);
        $destinationImage = $this->pathFull($imageName);

        return $this->go($sourceImage, $destinationImage, $this->config->get('full.width'), $this->config->get('full.height'), $format, $quality);
    }

    /**
     * @param string $imageName
     * @param string $format
     * @param int    $quality
     * @return mixed
     * @since 1.0
     */
    public function resizeMiddle($imageName, $format = null, $quality = 90)
    {
        $sourceImage = $this->getSource($imageName);

        $destinationImage = $this->pathMiddle($imageName);

        return $this->go($sourceImage, $destinationImage, $this->config->get('middle.width'), $this->config->get('middle.height'), $format, $quality);
    }

    /**
     * @param string $imageName
     * @param string $format
     * @param int    $quality
     * @return mixed
     * @since 1.0
     */
    public function resizeThumb($imageName, $format = null, $quality = 90)
    {
        $sourceImage = $this->getSource($imageName);

        $destinationImage = $this->pathThumb($imageName);

        return $this->go($sourceImage, $destinationImage, $this->config->get('thumb.width'), $this->config->get('thumb.height'), $format, $quality);
    }

    /**
     * @param string $imageName
     * @param int    $width
     * @param int    $height
     * @return string
     * @since 1.0
     */
    protected function getDestination($imageName, $width = null, $height = null)
    {
        if(!$width && !$height) {
            return $imageName;
        }
        // Исправление ошибки, если указано расширение файла в верхнем регистре
        $ext       = substr($imageName, strrpos($imageName, '.') + 1);
        $imageName = substr($imageName, 0, strrpos($imageName, '.'));

        return $imageName . '-' . $width . 'x' . $height . '.' . strtolower($ext);
    }

    /**
     * @param string $imageName
     * @return string
     * @since 1.0
     */
    protected function getSource($imageName)
    {
        if(!$imageName) {
            $imageName = $this->noImage;
        }
        // Исправление ошибки, если указано расширение файла в верхнем регистре
        $ext       = substr($imageName, strrpos($imageName, '.') + 1);
        $imageName = substr($imageName, 0, strrpos($imageName, '.')) . '.' . strtolower($ext);

        return '/' . $this->imagesDir . '/' . $imageName;
    }

    /**
     * @param string $sourceImage
     * @param string $destinationImage
     * @param int    $width
     * @param int    $height
     * @param string $format
     * @param int    $quality
     * @return string
     * @throws \PHPixie\Image\Exception
     */
    protected function go($sourceImage, $destinationImage, $width, $height, $format = null, $quality = 90)
    {
        if(file_exists($this->webRootDir . '/' . $destinationImage)) {
            return $destinationImage;
        }
        if(!file_exists($this->webRootDir . '/' . $sourceImage)) {
            $sourceImage = $this->getSource(null);
        }
        if(file_exists($this->webRootDir . '/' . $sourceImage)) {
            /** @var \PHPixie\Image\Drivers\Type\GD\Resource $image */
            $image = $this->image->read($this->webRootDir . '/' . $sourceImage);
            $image->fill($width, $height);
            if(!is_dir(dirname($this->webRootDir . '/' . $destinationImage))) {
                mkdir(dirname($this->webRootDir . '/' . $destinationImage), 0777, true);
            }
            $image->save($this->webRootDir . '/' . $destinationImage, $format, $quality);
        }

        return $destinationImage;
    }

}

