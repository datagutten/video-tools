<?php

namespace datagutten\video_tools;

use datagutten\tools\files\files;

class VideoTestData
{
    public static function get_path($file = null): string
    {
        return files::path_join(realpath(__DIR__.'/../tests/test_data'), $file);
    }

    public static function download_file($extension)
    {
        $local_file = self::get_path('sample.' . $extension);
        if (!file_exists($local_file))
            copy('http://techslides.com/demos/samples/sample.' . $extension, $local_file);
    }
}