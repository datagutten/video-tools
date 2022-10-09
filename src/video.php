<?php


namespace datagutten\video_tools;

use datagutten\tools\files\files;
use dependcheck;
use DependencyFailedException;
use Exception;
use FileNotFoundException;
use InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class video
 * Tools for video files
 */
class video
{
    /**
     * Convert duration in hours, minutes and seconds to seconds
     * https://stackoverflow.com/questions/4834202/convert-time-in-hhmmss-format-to-seconds-only
     * @param string $time Hours:Minutes:Seconds
     * @return int Seconds
     */
	public static function time_to_seconds($time)
	{
		$time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
		sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);

        return $hours * 3600 + $minutes * 60 + $seconds;
	}

    /**
     * Convert duration in seconds to hours, minutes and seconds
     * https://stackoverflow.com/a/3172368/2630074
     * @param int $seconds
     * @return string Hours:Minutes:Seconds
     */
	public static function seconds_to_time($seconds)
	{
		return sprintf('%02d:%02d:%02d',floor($seconds/3600),floor(($seconds/60) % 60),$seconds % 60);
	}

	/**
	 * Get the duration of a file using ffprobe or mediainfo
	 * @param string $file File name
	 * @param string $tool Tool to be used (default ffprobe)
	 * @return int Duration in seconds
	 * @throws exceptions\DurationNotFoundException Thrown when duration is not found
     * @throws DependencyFailedException Thrown when selected tool is not installed
	 */
	public static function duration(string $file, $tool='')
	{
	    $depend_check = new dependcheck();
	    if(empty($tool))
	        $tool = $depend_check->select_tool(['ffprobe', 'mediainfo']);

		if($tool=='ffprobe')
		{
            $process = new Process(['ffprobe', '-v', 'quiet', '-show_entries', 'format=duration', '-i', $file]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new exceptions\DurationNotFoundException('Error', 0, new ProcessFailedException($process));
            }

            $output = $process->getOutput();
            if(!preg_match("/duration=([0-9:\.]+)/",$output,$matches))
                throw new exceptions\DurationNotFoundException('Unable to find duration using ffprobe');

            return intval($matches[1]);
		}
		elseif($tool=='mediainfo')
		{
            $depend_check->depend('mediainfo');
            $duration_ms = trim(shell_exec("mediainfo --Inform=\"General;%Duration%\" \"$file\""));
			$duration=(int)floor($duration_ms/1000);
			if(empty($duration))
				throw new exceptions\DurationNotFoundException('Unable to get duration using mediainfo');
		}
		else
			throw new InvalidArgumentException('Invalid tool');

		return $duration;
	}

    /**
     * Calculate snapshot time steps
     * @param $file
     * @param int $steps Number of snapshots
     * @param bool $first Include first frame
     * @param bool $last Include last frame
     * @throws DependencyFailedException No tools available to get duration
     * @throws exceptions\DurationNotFoundException Duration not found
     * @return array Snapshot positions
     */
	public static function snapshotsteps($file,$steps=4,$first=false,$last=false)
	{
		$duration=self::duration($file);
		if(!is_numeric($steps))
		    throw new InvalidArgumentException('Steps is not numeric');
		if($duration<$steps)
			throw new InvalidArgumentException(sprintf('File duration is %d, not able to make %d snapshots', $duration, $steps));
		$step=(int)floor($duration/($steps+1)); //Get the step size
		$step_list=range($step,$duration,$step); //Make an array with the positions
		if($first!==false)
			array_unshift($step_list,1);
		if($last===false)
			array_pop($step_list); //remove the last position
		return $step_list;
	}

    /**
     * @param $file
     * @param array $positions Snapshot positions
     * @param string $output_dir absolute path to a folder where the snapshots are saved
     * @param string $tool Tool to create snapshots
     * @return array Snapshot image files
     * @throws FileNotFoundException File not found
     * @throws DependencyFailedException Tool to make snapshots not found
     * @throws Exception Snapshot creation failed
     */
	public static function snapshots(string $file, $positions=array(65,300,600,1000), $output_dir='', $tool='')
	{
        $depend_check = new dependcheck();
		if(!file_exists($file))
		    throw new FileNotFoundException($file);

		if(!is_array($positions))
			throw new InvalidArgumentException('Positions is not array');
        if(empty($tool))
            $tool = $depend_check->select_tool(['mplayer', 'ffmpeg']);
        else
            $depend_check->depend($tool);

		$snapshots=array();
		$path_info = pathinfo($file);
		if(empty($output_dir))
		    $output_dir = files::path_join($path_info['dirname'], 'snapshots', $path_info['basename']);

		if(!file_exists($output_dir))
			mkdir($output_dir,0777,true);

		foreach ($positions as $time)
		{
			if($time==3600)
				$time=3550;

			$snapshot_file = files::path_join($output_dir, str_pad($time,4,'0',STR_PAD_LEFT).".png");
            if(!file_exists($snapshot_file))
			{
				if($tool=='mplayer') //Create snapshots using mplayer
				{
					$log=shell_exec($cmd="mplayer -quiet -nosound -ss $time -vo png:z=9 -ao null -zoom -frames 1 \"$file\" 2>&1");
					if(file_exists($tmpfile='00000001.png'))
						rename($tmpfile,$snapshot_file);
				}
				elseif($tool=='ffmpeg') //Create snapshots using ffmpeg
				{
					$timestring=self::seconds_to_time($time);
					$log=shell_exec($cmd="ffmpeg  -stats -ss $timestring.000 -i \"$file\" -f image2 -vframes 1  \"$snapshot_file\" 2>&1"); //-loglevel error
				}
				else
                    throw new InvalidArgumentException(sprintf('%s is not a valid snapshot tool', $tool));

				if(file_exists($snapshot_file))
					$snapshots[]=$snapshot_file;
				else
					throw new Exception(sprintf('Failed to create snapshot:\nCommand: %s\nLog:\n%s',$cmd,$log));
			}
			else
				$snapshots[]=$snapshot_file;
		}
		if(empty($snapshots))
			$snapshots=false;
		return $snapshots;
	}

    /**
     * Parse season and episode string
     * @param string $episode_string
     * @return array
     */
    public static function parse_episode(string $episode_string)
    {
        preg_match('/(?:S([0-9]+))?(?:EP?([0-9]+))?/', $episode_string, $matches);
        if (!empty($matches[1]) && !empty($matches[2]))
            return ['season' => intval($matches[1]), 'episode' => intval($matches[2])];
        elseif ($matches[0][0] == 'S')
            return ['season' => intval($matches[1])];
        elseif ($matches[0][0] == 'E')
            return ['episode' => intval($matches[2])];
		else
			throw new InvalidArgumentException('Unable to parse episode string');
    }
}
