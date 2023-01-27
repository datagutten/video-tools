<?php

namespace datagutten\video_tools\tests;

use datagutten\tools\files\files;
use datagutten\video_tools\exceptions\SubtitleConversionException;
use datagutten\video_tools\subtitles\ttml_to_srt;
use datagutten\video_tools\subtitles\vtt_to_srt;
use PHPUnit\Framework\TestCase;

class vtt_to_srtTest extends TestCase
{

    /**
     * @throws SubtitleConversionException
     */
    public function testConvert_file()
    {
        $expected_output_file = files::path_join(__DIR__, 'test_data', 'test.srt');
        if (file_exists($expected_output_file))
            unlink($expected_output_file);
        $this->assertFileDoesNotExist($expected_output_file);

        $input_file = files::path_join(__DIR__, 'test_data', 'test.vtt');
        $this->assertFileExists($input_file);
        $output_file = vtt_to_srt::convert_file($input_file);
        $this->assertSame(realpath($expected_output_file), realpath($output_file));
        $this->assertFileExists($output_file);
        $this->assertFileExists($expected_output_file);
        $this->assertFileEquals(files::path_join(__DIR__, 'test_data', 'test_vtt_converted.srt'), $output_file);
        unlink($output_file);
    }

    public function testEnd_time()
    {
        $convert = new ttml_to_srt();
        $end = $convert->end_time('00:00:05.320', '00:00:03.200');
        $this->assertSame('00:00:08,520000', $end);
    }

    public function testConvert()
    {
        $convert = new vtt_to_srt();
        $input_file = files::path_join(__DIR__, 'test_data', 'test.vtt');
        $ttml = file_get_contents($input_file);
        $srt = $convert->convert($ttml);
        $this->assertStringContainsString('00:00:04,680 --> 00:00:10,160', $srt);
        $this->assertStringContainsString('på Haraldsplass sykehus.', $srt);
    }
}
