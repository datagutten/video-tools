<?php


namespace datagutten\video_tools;

use DependencyFailedException;
use FileNotFoundException;


class video_duration_check extends video
{
    /**
     * Check if the duration of a file matches the given duration
     *
     * @param int $duration_check Duration to be checked in seconds
     * @param int $duration_reference Expected duration in seconds
     * @param int $tolerance Duration tolerance in seconds (default 90)
     * @return bool
     * @throws exceptions\WrongDurationException
     */
    private static function check_duration($duration_check, $duration_reference, $tolerance = 90)
    {
        if ($duration_reference == $duration_check) //Duration is correct
            return true;
        //Duration is within tolerance
        elseif ($duration_reference > $duration_check && $duration_reference - $duration_check <= $tolerance)
            return true;
        elseif ($duration_check > $duration_reference && $duration_check - $duration_reference <= $tolerance)
            return true;
        else {
            $msg = sprintf('Wrong duration: File duration %s is outside tolerance from %s', $duration_check, $duration_reference);
            throw new exceptions\WrongDurationException($msg);
        }
    }

    /**
     * Check if the file exists and has valid duration
     * @param string $file File to be checked
     * @param int $duration_reference Expected duration
     * @return string File name
     * @throws FileNotFoundException File not found
     * @throws exceptions\WrongDurationException File duration does not match reference
     * @throws exceptions\DurationNotFoundException Unable to get duration
     * @throws DependencyFailedException
     */
    public static function check_file_duration($file, $duration_reference)
    {
        if (!file_exists($file))
            throw new FileNotFoundException($file);
        if (filesize($file) == 0) //Check if the file is empty
        {
            unlink($file);
            throw new exceptions\WrongDurationException('File is empty');
        }
        try {
            $duration_file = self::duration($file); //Get file duration
            self::check_duration($duration_file, $duration_reference);
            return $file;
        } catch (exceptions\WrongDurationException $e) {
            rename($file, $file . ".wrong_duration");
            throw $e;
        }
        catch (exceptions\DurationNotFoundException $e) {
            rename($file, $file . ".bad_duration");
            throw $e;
        }
    }
}