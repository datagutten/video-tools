<?php
/**
 * Created by PhpStorm.
 * User: Anders
 * Date: 20.01.2019
 * Time: 15.27
 */

class DownloadFailedException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

require_once '../tools/dependcheck.php';
require_once '../tools/filnavn.php';
require_once 'video_duration_check.php';

class video_download
{
    public $depend_check;
    public $video;
    public $duration_check;

    function __construct()
    {
        $this->depend_check = new dependcheck;
        $this->video = new video;
        $this->duration_check = new video_duration_check;
    }

    /**
     * Generate file and folder name for TV program
     *
     * @param $program
     * @return array
     * @throws Exception
     */
    public static function season_episode($program)
    {
        if (empty($program) || !is_array($program))
            throw new Exception('Argument must be a non-empty array');
        if (!isset($program['title']))
            throw new Exception('title must be set');

        if (empty($program['series'])) //Not part of a series
        {
            if (isset($program['productionYear']))
                $program['title'] = sprintf('%s (%s)', $program['title'], $program['productionYear']);
            return array('folder' => '', 'file' => $program['title']);
        } else
            $file = $program['series']; //Start file name with series name


        if (!empty($program['season'])) //Series has numbered seasons
        {
            $file .= sprintf(' S%02d', $program['season']); //Add season number
            $episode_prefix = 'E';
        } else
            $episode_prefix = ' EP';

        if (!empty($program['season_title']) && stripos($program['season_title'], 'sesong') === false)
            $file .= sprintf(' - %s', $program['season_title']); //Add season title
        $folder = $file; //Folder is file before episode information

        if (!empty($program['episode']))
            $file .= sprintf('%s%02d', $episode_prefix, $program['episode']);


        if (!empty($program['episode_title']) && stripos($program['episode_title'], 'episode') === false)
            $file .= sprintf(' - %s', $program['episode_title']);

        return array('folder' => $folder, 'file' => filnavn($file));
    }

    /**
     * Mux the a file to mkv using mkvmerge
     *
     * @param string|array $filename String with a single file or array with multiple files to be combined
     * @param string $mkv_file File name for muxed file. Required when combining files
     * @return string Output from mkvmerge
     * @throws FileNotFoundException
     * @throws Exception
     * @throws DependencyFailedException
     */
    public function mkvmerge($filename, $mkv_file = null)
    {
        $this->depend_check->require('mkvmerge');

        if(empty($mkv_file))
        {
            if (is_array($filename))
                throw new Exception('MKV file name need to be specified when using multiple input files');
            else
            {
                $pathinfo = pathinfo($filename);
                $mkv_file = sprintf('%s/%s.mkv', $pathinfo['dirname'], $pathinfo['filename']);
                $filename = array($filename);
            }
        }

        $duration = 0;
        //Check input files
        $files = '';
        foreach ($filename as $file) {
            try {
                $duration += $this->video->duration($file);
            }
            catch (Exception $e)
            {
                echo $e->getMessage()."\n";
            }
            if(!file_exists($file))
                throw new FileNotFoundException($file);
            $file = sprintf('"%s"', $file); //Add double quotes to the file name
            if (empty($files))
                $files = $file;
            else
                $files .= ' + ' . $file;
        }

        try {
            return $this->duration_check->check_file_duration($mkv_file, $duration);
        }
        catch (FileNotFoundException | WrongDurationException $e) //No problem
        {
            echo $e->getMessage()."\n";
        }

        $cmd = sprintf('mkvmerge -o "%s" %s', $mkv_file, $files);

        echo "Creating mkv\n";
        if (file_exists($mkv_file . '.chapters.txt'))
            $cmd .= sprintf(' --chapter-charset UTF-8 --chapters "%s.chapters.txt"', $mkv_file);
        shell_exec($cmd . " 2>&1");

        return $this->duration_check->check_file_duration($mkv_file, $duration);
    }

    /**
     * Download a stream using ffmpeg
     *
     * @param string $stream Stream URL to be downloaded
     * @param string $file File name to be saved (without extension, mp4 will be appended)
     * @param null $duration Expected duration, used for verifying the downloaded file
     * @param int $loglevel ffmpeg log level
     * @return string
     * @throws DownloadFailedException
     * @throws FileNotFoundException
     */
    public static function ffmpeg_download($stream, $file, $duration = null, $loglevel = 16)
    {
        require_once 'video_duration_check.php';
        $check = new video_duration_check;
        if (!empty($duration)) {
            try {
                if (file_exists($file . '.mp4'))
                    return $check->check_file_duration($file . '.mp4', $duration);
                elseif (file_exists($file . '.mp4'))
                    return $check->check_file_duration($file . '.mkv', $duration);
                //File is already downloaded and has valid duration
                return $file;
            } catch (WrongDurationException $e) {
                var_dump($e);
                echo $e->getMessage() . "\n";
            }
        }

        $file = $file . '.mp4';
        printf("Downloading to %s\n", $file);

        $cmd = sprintf('ffmpeg -loglevel %d -i "%s" -c copy "%s" 2>&1', $loglevel, $stream, $file); //loglevel 8=fatal

        echo shell_exec($cmd);

        if (!empty($duration)) {
            try {
                $check->check_file_duration($file, $duration);
            } catch (WrongDurationException $e) // FileNotFoundException is not caught because it could be a fatal error
            {
                throw new DownloadFailedException($e);
            }
        }

        return $file;
    }
}