<?php

namespace datagutten\video_tools;

use datagutten\tools\files\files;
use FileNotFoundException;

class VideoTestData
{
    public static function get_path($file = null): string
    {
        return files::path_join(sys_get_temp_dir(), $file);
    }

    /**
     * Get sample video file
     * @param string $extension Video file extension
     * @return void
     * @throws FileNotFoundException
     */
    public static function download_file(string $extension): string
    {
        $local_file = self::get_path('sample.' . $extension);
        if (!file_exists($local_file))
        {
            $file = __DIR__ . '/../tests/test_data/sample.' . $extension;
            if (!file_exists($file))
                throw new FileNotFoundException($file);
            copy($file, $local_file);
        }
        return $local_file;
    }
}