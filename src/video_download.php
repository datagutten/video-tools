<?php


namespace datagutten\video_tools;

use dependcheck;
use DependencyFailedException;
use FileNotFoundException;
use InvalidArgumentException;
use Symfony\Component\Process\Process;

class video_download
{
    /**
     * Generate file and folder name for TV program
     *
     * @param $program
     * @return array
     * @deprecated Use EpisodeFormat class
     */
    public static function season_episode($program)
    {
        if (empty($program) || !is_array($program))
            throw new InvalidArgumentException('Argument must be a non-empty array');
        if (!isset($program['title']))
            throw new InvalidArgumentException('title must be set');

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
     * @throws DependencyFailedException
     * @throws exceptions\DurationNotFoundException
     * @throws exceptions\WrongDurationException
     */
    public static function mkvmerge($filename, $mkv_file = '')
    {
        $depend_check = new dependcheck();
        $depend_check->depend('mkvmerge');

        if(empty($mkv_file))
        {
            if (is_array($filename))
                throw new InvalidArgumentException('MKV file name need to be specified when using multiple input files');
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
                $duration += video::duration($file);
            }
            catch (exceptions\DurationNotFoundException $e)
            {
                echo 'Unable to get duration of input file: '.$e->getMessage()."\n";
            }
            if(!file_exists($file))
                throw new FileNotFoundException($file);
            $file = sprintf('"%s"', $file); //Add double quotes to the file name
            if (empty($files))
                $files = $file;
            else
                $files .= ' + ' . $file;
        }

        //Check if the file already exists and is valid
        try {
            return video_duration_check::check_file_duration($mkv_file, $duration);
        }
        catch (FileNotFoundException | exceptions\WrongDurationException $e) //No problem
        {
            echo $e->getMessage()."\n";
        }

        $cmd = sprintf('mkvmerge -o "%s" %s', $mkv_file, $files);

        echo "Creating mkv\n";
        if (file_exists($mkv_file . '.chapters.txt'))
            $cmd .= sprintf(' --chapter-charset UTF-8 --chapters "%s.chapters.txt"', $mkv_file);
        shell_exec($cmd . " 2>&1");

        return video_duration_check::check_file_duration($mkv_file, $duration);
    }

    /**
     * Download a stream using ffmpeg
     *
     * @param string $stream Stream URL to be downloaded
     * @param string $file File name to be saved (without extension, mp4 will be appended)
     * @param null $duration Expected duration, used for verifying the downloaded file
     * @param bool mkvmerge Merge the downloaded file to mkv
     * @param int $loglevel ffmpeg log level
     * @param string $extension Extension to append to the file name
     * @return string
     * @throws FileNotFoundException
     * @throws DependencyFailedException
     * @throws exceptions\DurationNotFoundException
     * @throws exceptions\WrongDurationException
     */
    public static function ffmpeg_download($stream, $file, $duration = null, $mkvmerge = true, $loglevel = 16, $extension = 'mp4')
    {
        $check = new video_duration_check;
        if (!empty($duration)) {
            try {
                //Check if file is already downloaded and has valid duration
                if (file_exists($file . '.mp4'))
                    return $check->check_file_duration($file . '.mp4', $duration);
                elseif (file_exists($file . '.mkv'))
                    return $check->check_file_duration($file . '.mkv', $duration);
            } catch (exceptions\WrongDurationException $e) {
                echo $e->getMessage() . "\n";
            }
        }
        if (!empty($extension))
            $file = $file . '.' . $extension;

        printf("Downloading to %s\n", $file);

        $cmd = sprintf('ffmpeg -loglevel %d -i "%s" -c copy "%s" 2>&1', $loglevel, $stream, $file); //loglevel 8=fatal

        echo shell_exec($cmd);

        if (!empty($duration)) {
            $check->check_file_duration($file, $duration);
            if(pathinfo($file, PATHINFO_EXTENSION)==='mp4' && $mkvmerge) {
                $file_mkv = self::mkvmerge($file);
                unlink($file);
                return $file_mkv;
            }
            else
                return $file;
        }

        return $file;
    }

    public static function ffmpeg($input_file, $output, $codec = null, $run = true)
    {
        $cmd = ['ffmpeg', '-i', $input_file];
        if ($codec)
            $cmd += ['-c', $codec];

        $cmd[] = $output;

        $process = new Process($cmd);
        if ($run)
            return $process->run();
        else
            return $process;
    }
}