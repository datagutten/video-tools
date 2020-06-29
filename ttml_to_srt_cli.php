<?php
/**
 * Created by PhpStorm.
 * User: Anders
 * Date: 27.01.2019
 * Time: 12.43
 */

use datagutten\video_tools\subtitles;
use datagutten\video_tools\exceptions;


require __DIR__.'/vendor/autoload.php';
try {
    $ttml_to_srt = new subtitles\ttml_to_srt();
    $ttml_to_srt->convert_file($argv[1]);
}
catch (exceptions\SubtitleConversionException $e)
{
    echo $e->getMessage()."\n";
}