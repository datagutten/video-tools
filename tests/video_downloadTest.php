<?php

namespace datagutten\video_tools\tests;

use datagutten\tools\files\files;
use datagutten\video_tools\video;
use datagutten\video_tools\video_download;
use PHPUnit\Framework\TestCase;

class video_downloadTest extends TestCase
{
    private string $test_file;
    private string $test_file_convert;

    public function setUp(): void
    {
        $this->test_file = files::path_join(__DIR__, 'test_data', 'Reklame Kornmo Treider 41.mp4');
        $this->test_file_convert = files::path_join(__DIR__, 'test_data', 'Reklame Kornmo Treider 41.mp2');
        self::tearDown();
    }

    public function tearDown(): void
    {
        if (file_exists($this->test_file_convert))
            unlink($this->test_file_convert);
    }

    public function testFfmpeg()
    {
        $this->assertFileDoesNotExist($this->test_file_convert);
        $process = video_download::ffmpeg($this->test_file, $this->test_file_convert);
        $this->assertFileExists($this->test_file_convert, $process->getErrorOutput());
        $this->assertEquals(video::duration($this->test_file), video::duration($this->test_file_convert));
    }
}
