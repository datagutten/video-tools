<?php

use datagutten\video_tools\video_duration_check;
use PHPUnit\Framework\TestCase;
use datagutten\video_tools\VideoTestData;
use datagutten\video_tools\exceptions;

class DurationTest extends TestCase
{

    public function setUp(): void
    {
        VideoTestData::download_file('mp4');
    }

    public function testCheck_file_duration()
    {
        try
        {
            $file = video_duration_check::check_file_duration(VideoTestData::get_path('sample.mp4'), 5);
            $this->assertNotEmpty($file);
        }
        catch (exceptions\WrongDurationException $e)
        {
            $this->fail($e->getMessage());
        }
    }

    public function testCheck_file_durationWithinTolerance()
    {
        try
        {
            $file = video_duration_check::check_file_duration(VideoTestData::get_path('sample.mp4'), 10);
            $this->assertNotEmpty($file);
        }
        catch (exceptions\WrongDurationException $e)
        {
            $this->fail($e->getMessage());
        }
    }

    public function testShorter()
    {
        try
        {
            $file = video_duration_check::check_file_duration(VideoTestData::get_path('sample.mp4'), 4);
            $this->assertNotEmpty($file);
        }
        catch (exceptions\WrongDurationException $e)
        {
            $this->fail($e->getMessage());
        }
    }

    public function testCheck_file_durationOutsideTolerance()
    {
        $this->expectException(exceptions\WrongDurationException::class);
        $this->expectExceptionMessage('File duration 5 is outside tolerance from 1000');
        video_duration_check::check_file_duration(VideoTestData::get_path('sample.mp4'), 1000);
    }

    public function testCheckEmptyFile()
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'video');
        $this->assertFileExists($temp_file);
        $this->expectException(exceptions\WrongDurationException::class);
        $this->expectExceptionMessage('File is empty');
        video_duration_check::check_file_duration($temp_file, 5);
        $this->assertFileDoesNotExist($temp_file);
    }

    public function testMissing()
    {
        $this->expectException(FileNotFoundException::class);
        video_duration_check::check_file_duration('foo', 5);
    }

    public function testDurationNotFound()
    {
        $this->expectException(exceptions\DurationNotFoundException::class);
        $temp_file = tempnam(sys_get_temp_dir(), 'video');
        file_put_contents($temp_file, 'test');
        video_duration_check::check_file_duration($temp_file, 5);
    }
}
