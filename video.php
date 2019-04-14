<?Php
//Tools for video files
class video
{
	private $dependcheck;
	public $error;
	function __construct()
	{
		require_once '../tools/dependcheck.php';
		$this->dependcheck=new dependcheck;
	}
	function time_to_seconds($time)
	{
		//https://stackoverflow.com/questions/4834202/convert-time-in-hhmmss-format-to-seconds-only
		$time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
		sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);

		$time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
		return $time_seconds;
	}
	function seconds_to_time($seconds)
	{
		//https://stackoverflow.com/a/3172368/2630074
		return sprintf('%02d:%02d:%02d',floor($seconds/3600),floor(($seconds/60) % 60),$seconds % 60);
	}

	/**
	 * Get the duration of a file using ffprobe or mediainfo
	 * @param string $file File name
	 * @param string $tool Tool to be used (default ffprobe)
	 * @return int Duration in seconds
	 * @throws Exception
	 */
	public function duration($file, $tool='ffprobe')
	{
		if($tool=='ffprobe' && $this->dependcheck->depend('ffprobe')===true)
		{
			$return=shell_exec("ffprobe -i \"$file\" 2>&1");
			if(!preg_match("/Duration: ([0-9:\.]+)/",$return,$matches))
				throw new Exception('Unable to find duration using ffprobe');
			$duration = $this->time_to_seconds($matches[1]);
		}
		elseif($tool=='mediainfo' && $this->dependcheck->depend('mediainfo')===true)
		{
			$duration=floor(shell_exec("mediainfo --Inform=\"General;%Duration%\" \"$file\"")/1000);
			if(empty($duration))
				throw new Exception('Unable to get duration using mediainfo');
		}
		else
			throw new Exception('Invalid tool or no valid tools installed');

		return $duration;
	}
	
	public function snapshotsteps($file,$steps=4,$first=false,$last=false) //Calculate snapshot time steps
	{
		$duration=$this->duration($file);
		if($duration===false || $duration<$steps)
			return false;
		$step=floor($duration/($steps+1)); //Get the step size
		
		$steplist=range($step,$duration,$step); //Make an array with the positions
		if(!is_array($steplist))
			return false;
		if($first!==false)
			array_unshift($steplist,1);
		if($last===false)
			array_pop($steplist); //remove the last position
		return $steplist;

	}
	
	public function snapshots($file,$positions=array(65,300,600,1000),$snapshotdir="snapshots/",$tool='ffmpeg') //Second argument can be an array with time positions or a number of snapshots to be created
	{
		if(!file_exists($file))
		{
			trigger_error("File not found: $file",E_USER_WARNING);
			return false;	
		}
		switch($tool) //Check that the selected tool is valid
		{
			case 'ffmpeg':
			case 'mplayer':
			break;
			default: trigger_error("$tool is not a valid snapshot tool",E_USER_ERROR);		
		}
		if(!is_array($positions))
		{
			trigger_error("Positions is not array",E_USER_WARNING);
			return false;
		}

		if($this->dependcheck->depend($tool)!==true)
			trigger_error("$tool not found",E_USER_ERROR);

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
				$starttime=time();
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
				$elapsedtime=time()-$starttime;

				if(file_exists($snapshotfile))
					$snapshots[]=$snapshotfile;
				else
				{
					$this->error=sprintf('Failed to create snapshot:\nCommand: %s\nLog:\n%s',$cmd,$log);
					return false;
				}
			}
			else
				$snapshots[]=$snapshotfile;
		}
		if(empty($snapshots))
			$snapshots=false;
		return $snapshots; //Returnerer et array med bildenes filnavn
	}	
}
