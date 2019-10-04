<?Php
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

		$time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
		return $time_seconds;
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
	 * @throws Exception|DependencyFailedException
	 */
	public static function duration($file, $tool='ffprobe')
	{
	    $depend_check = new dependcheck();
		if($tool=='ffprobe')
		{
            $depend_check->depend('ffprobe');
			$return=shell_exec("ffprobe -i \"$file\" 2>&1");
			if(!preg_match("/Duration: ([0-9:\.]+)/",$return,$matches))
				throw new Exception('Unable to find duration using ffprobe');
			$duration = self::time_to_seconds($matches[1]);
		}
		elseif($tool=='mediainfo')
		{
            $depend_check->depend('mediainfo');
			$duration=floor(shell_exec("mediainfo --Inform=\"General;%Duration%\" \"$file\"")/1000);
			if(empty($duration))
				throw new Exception('Unable to get duration using mediainfo');
		}
		else
			throw new Exception('Invalid tool or no valid tools installed');

		return $duration;
	}

    /**
     * Calculate snapshot time steps
     * @param $file
     * @param int $steps Number of snapshots
     * @param bool $first Include first frame
     * @param bool $last Include last frame
     * @return array
     * @throws Exception|InvalidArgumentException
     */
	public static function snapshotsteps($file,$steps=4,$first=false,$last=false)
	{
		$duration=self::duration($file);
		if($duration<$steps)
			throw new InvalidArgumentException(sprintf('File duration is %d, not able to make %d snapshots', $duration, $steps));
		$step=floor($duration/($steps+1)); //Get the step size
		
		$step_list=range($step,$duration,$step); //Make an array with the positions
		if(!is_array($step_list))
			throw new Exception(sprintf('Unable to create steps'));
		if($first!==false)
			array_unshift($step_list,1);
		if($last===false)
			array_pop($step_list); //remove the last position
		return $step_list;
	}

    /**
     * @param $file
     * @param array $positions Snapshot positions
     * @param string $snapshotdir
     * @param string $tool Tool to create snapshots
     * @return array Snapshot image files
     * @throws FileNotFoundException|InvalidArgumentException|Exception
     */
	public function snapshots($file,$positions=array(65,300,600,1000),$snapshotdir="snapshots/",$tool='ffmpeg')
	{
        $depend_check = new dependcheck();
		if(!file_exists($file))
		    throw new FileNotFoundException($file);

		if(!is_array($positions))
			throw new InvalidArgumentException('Positions is not array');

		$snapshots=array();
		$basename=basename($file);
		$snapshotdir=$snapshotdir."/".$basename."/";

		if(!file_exists($snapshotdir))
			mkdir($snapshotdir,0777,true);

		foreach ($positions as $time)
		{
			if($time==3600)
				$time=3550;
			if(!file_exists($snapshotfile=$snapshotdir.str_pad($time,4,'0',STR_PAD_LEFT).".png"))
			{
                $depend_check->depend($tool);
				if($tool=='mplayer') //Create snapshots using mplayer
				{
					$log=shell_exec($cmd="mplayer -quiet -nosound -ss $time -vo png:z=9 -ao null -zoom -frames 1 \"$file\" 2>&1");
					if(file_exists($tmpfile='00000001.png'))
						rename($tmpfile,$snapshotfile);
				}
				elseif($tool=='ffmpeg') //Create snapshots using ffmpeg
				{
					$timestring=$this->seconds_to_time($time);
					$log=shell_exec($cmd="ffmpeg  -stats -ss $timestring.000 -i \"$file\" -f image2 -vframes 1  \"$snapshotfile\" 2>&1"); //-loglevel error
				}
				else
                    throw new InvalidArgumentException(sprintf('%s is not a valid snapshot tool', $tool));

				if(file_exists($snapshotfile))
					$snapshots[]=$snapshotfile;
				else
					throw new Exception(sprintf('Failed to create snapshot:\nCommand: %s\nLog:\n%s',$cmd,$log));
			}
			else
				$snapshots[]=$snapshotfile;
		}
		if(empty($snapshots))
			$snapshots=false;
		return $snapshots;
	}	
}
