<?Php
//Tools for video files
class video
{
	private $dependcheck;
	function __construct()
	{
		require_once 'dependcheck.php';	
		$this->dependcheck=new dependcheck;
		date_default_timezone_set('GMT');
	}
	
	public function duration($file,$tool='ffprobe') //Return the duration of $file in seconds
	{
		if($tool=='ffprobe' && $this->dependcheck->depend('ffprobe'))
		{
			//$duration=floor(trim($return=shell_exec("ffprobe -i \"$file\" -show_entries format=duration -v quiet -of csv=\"p=0\"")));
			$return=shell_exec("ffprobe -i \"$file\" 2>&1");
			preg_match("/Duration: ([0-9:\.]+)/",$return,$matches);
			$duration=strtotime($matches[1],0);
		}
		elseif($tool=='mediainfo' && $this->dependcheck->depend('mediainfo'))
			$duration=floor(shell_exec("mediainfo --Inform=\"General;%Duration%\" \"$file\"")/1000);
		if(empty($duration)) //If duration is not set or zero something has failed
			return false;
		else
			return $duration;
	}
	
	public function snapshotsteps($file,$steps=4,$first=false,$last=false) //Calculate snapshot time steps
	{
		$duration=$this->duration($file);
		if($duration===false)
			return false;
		$step=floor($duration/($steps+1)); //Get the step size
		
		$steplist=range($step,$duration,$step); //Make an array with the positions

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
			echo $time."s\n";
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
					$timestring=date('H:i:s',$time);
					$log=shell_exec($cmd="ffmpeg  -stats -ss $timestring.000 -i \"$file\" -f image2 -vframes 1  \"$snapshotfile\" 2>&1"); //-loglevel error
				}
				$elapsedtime=time()-$starttime;

				if(file_exists($snapshotfile))
					$snapshots[]=$snapshotfile;
				else
				{
					trigger_error(nl2br(htmlentities("Failed to create snapshot: ".trim($log))),E_USER_WARNING);
					echo $cmd."<br />\n";
					continue;
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