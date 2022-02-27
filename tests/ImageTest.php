<?php

namespace datagutten\video_tools\tests;

use datagutten\video_tools\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{

    public function testCrop()
    {
        $image = Image::createtruecolor(800, 600);
        $crop = $image->crop(100, 50, 600, 400);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals(600, $crop->width);
        $this->assertEquals(400, $crop->height);
    }

    public function testResize()
    {
        $image = Image::createtruecolor(800, 600);
        $resize = $image->resize(500, 500);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals(500, $resize->width);
        $this->assertEquals(500, $resize->height);
    }
}
