<?php /** @noinspection PhpComposerExtensionStubsInspection */


namespace datagutten\video_tools;

use FileNotFoundException;
use InvalidArgumentException;

/**
 * GD image wrapper
 */
class Image
{
    /**
     * @var resource Image resource
     */
    public $im;
    /**
     * @var string File name
     */
    public string $file;
    /**
     * @var int Image width
     */
    public int $width;
    /**
     * @var int Image height
     */
    public int $height;

    public function __construct($im, $file = null)
    {
        if (empty($im))
            throw new InvalidArgumentException('Argument is not resource');
        $this->im = $im;
        if (!empty($file))
            $this->file = $file;
        $this->width = imagesx($im);
        $this->height = imagesy($im);
    }

    /**
     * Create a new true color image
     * @link https://php.net/manual/en/function.imagecreatetruecolor.php
     * @param int $width Image width
     * @param int $height Image height
     * @return static
     */
    public static function createtruecolor(int $width, int $height): Image
    {
        return new static(imagecreatetruecolor($width, $height));
    }

    /**
     * Create a new image from file
     * @param $filename string Path to the PNG image.
     * @return static Image instance
     * @throws FileNotFoundException
     */
    public static function from_png(string $filename): Image
    {
        if (!file_exists($filename))
            throw new FileNotFoundException($filename);
        return new static(imagecreatefrompng($filename), $filename);
    }

    /**
     * Create a new image from file
     * @param $filename string Path to the PNG image.
     * @return static Image instance
     * @throws FileNotFoundException
     */
    public static function from_jpg(string $filename): Image
    {
        if (!file_exists($filename))
            throw new FileNotFoundException($filename);
        return new static(imagecreatefromjpeg($filename), $filename);
    }

    /**
     * Crop the image
     * @param int $x Crop box X
     * @param int $y Crop box Y
     * @param int $width Crop box width
     * @param int $height Crop box height
     * @return static New Image instance with the cropped image
     */
    function crop(int $x, int $y, int $width, int $height): Image
    {
        $crop = static::createtruecolor($width, $height);
        imagecopy($crop->im, $this->im, 0, 0, $x, $y, $width, $height);
        return $crop;
    }

    /**
     * Resize the image
     * @param int $width New width
     * @param int $height New height
     * @return static New Image instance with the resized image
     */
    public function resize(int $width, int $height): Image
    {
        $resize = static::createtruecolor($width, $height);
        imagecopyresized($resize->im, $this->im, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        return $resize;
    }

    /**
     * Output a PNG image to either the browser or a file
     * @param ?string $file
     */
    public function png(string $file = null): bool
    {
        return imagepng($this->im, $file);
    }

    /**
     * Get the index of the color of a pixel
     * @link https://php.net/manual/en/function.imagecolorat.php
     * @param int $x x-coordinate of the point.
     * @param int $y y-coordinate of the point.
     * @return int|false the index of the color or <b>FALSE</b> on failure
     */
    public function color_at(int $x, int $y): int
    {
        return imagecolorat($this->im, $x, $y);
    }
}